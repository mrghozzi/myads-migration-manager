<?php

declare(strict_types=1);

use App\Helpers\Hooks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    Route::get('/admin/migration-manager', function () {
        $migrationsPath = base_path('database/migrations');
        $files = collect(File::files($migrationsPath));
        
        $pluginDirs = File::directories(base_path('plugins'));
        foreach ($pluginDirs as $pluginDir) {
            $pluginMigrationsPath = $pluginDir . '/database/migrations';
            if (File::isDirectory($pluginMigrationsPath)) {
                $files = $files->merge(File::files($pluginMigrationsPath));
            }
        }
        
        $allMigrations = $files->map(function ($file) {
            return $file->getFilenameWithoutExtension();
        })->sort()->values();

        $ranMigrations = DB::table('migrations')->pluck('migration');
        
        $pendingMigrations = $allMigrations->diff($ranMigrations)->values();
        $ghostMigrations = $ranMigrations->diff($allMigrations)->values();
        
        // --- Structural Verification (Missing Tables) ---
        $missingTables = collect();
        foreach ($files as $file) {
            $content = File::get($file->getPathname());
            // Regex to find table names passed to Schema::create('table_name', ...)
            if (preg_match_all('/Schema::create\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
                $tablesInFile = $matches[1];
                foreach ($tablesInFile as $tableName) {
                    if (!Schema::hasTable($tableName)) {
                        $missingTables->push([
                            'table' => $tableName,
                            'migration' => $file->getFilenameWithoutExtension()
                        ]);
                    }
                }
            }
        }
        
        $isHealthy = $pendingMigrations->isEmpty() && $ghostMigrations->isEmpty() && $missingTables->isEmpty();
        $totalFiles = $allMigrations->count();
        $totalRan = $ranMigrations->count();

        return view('migration_manager::index', compact('pendingMigrations', 'ghostMigrations', 'missingTables', 'isHealthy', 'totalFiles', 'totalRan'));
    })->name('admin.migration-manager.index');

    Route::post('/admin/migration-manager/run', function (Request $request) {
        $migration = $request->input('migration');
        
        if (!$migration) {
            return redirect()->route('admin.migration-manager.index')->with('error', 'لم يتم تحديد ملف التهجير.');
        }

        // Find the actual path of the migration
        $basePath = base_path('database/migrations/' . $migration . '.php');
        $migrationPath = null;

        if (File::exists($basePath)) {
            $migrationPath = 'database/migrations/' . $migration . '.php';
        } else {
            $pluginDirs = File::directories(base_path('plugins'));
            foreach ($pluginDirs as $pluginDir) {
                $pluginName = basename($pluginDir);
                $pluginPath = base_path('plugins/' . $pluginName . '/database/migrations/' . $migration . '.php');
                if (File::exists($pluginPath)) {
                    $migrationPath = 'plugins/' . $pluginName . '/database/migrations/' . $migration . '.php';
                    break;
                }
            }
        }

        if (!$migrationPath) {
            return redirect()->route('admin.migration-manager.index')->with('error', 'ملف التهجير غير موجود في النظام.');
        }

        try {
            // Run the specific migration using Artisan
            Artisan::call('migrate', [
                '--path' => $migrationPath,
                '--force' => true
            ]);
            
            return redirect()->route('admin.migration-manager.index')->with('success', 'تم تنفيذ ملف التهجير ' . $migration . ' بنجاح.');
        } catch (\Exception $e) {
            return redirect()->route('admin.migration-manager.index')->with('error', 'حدث خطأ أثناء التنفيذ: ' . $e->getMessage());
        }
    })->name('admin.migration-manager.run');

    Route::post('/admin/migration-manager/delete-ghost', function (Request $request) {
        $migration = $request->input('migration');
        
        if (!$migration) {
            return redirect()->route('admin.migration-manager.index')->with('error', 'لم يتم تحديد ملف التهجير.');
        }

        try {
            DB::table('migrations')->where('migration', $migration)->delete();
            return redirect()->route('admin.migration-manager.index')->with('success', 'تم حذف السجل الخاص بـ ' . $migration . ' من قاعدة البيانات بنجاح.');
        } catch (\Exception $e) {
            return redirect()->route('admin.migration-manager.index')->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    })->name('admin.migration-manager.delete-ghost');
});

View::addNamespace('migration_manager', __DIR__ . '/views');

Hooks::add_action('admin_sidebar_menu', function (): void {
    $url = route('admin.migration-manager.index');
    $isActive = request()->routeIs('admin.migration-manager.*');
    $linkClass = $isActive ? 'nxl-link active' : 'nxl-link';

    echo '<li class="nxl-item">'
        . '<a href="' . e($url) . '" class="' . e($linkClass) . '">'
        . '<span class="nxl-micon"><i class="feather-database"></i></span>'
        . '<span class="nxl-mtext">إدارة التهجير (Migrations)</span>'
        . '</a>'
        . '</li>';
});
