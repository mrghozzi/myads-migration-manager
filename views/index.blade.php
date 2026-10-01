@extends('admin::layouts.admin')

@section('title', 'إدارة التهجير (Migrations)')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <div class="card {{ $isHealthy ? 'border-success' : 'border-danger' }}">
            <div class="card-header {{ $isHealthy ? 'bg-success text-white' : 'bg-danger text-white' }}">
                <h4 class="card-title text-white mb-0">
                    <i class="feather-activity mr-1"></i> فحص سلامة قاعدة البيانات
                </h4>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2">
                        <h5 class="text-muted">حالة قاعدة البيانات</h5>
                        @if($isHealthy)
                            <h3 class="text-success"><i class="feather-check-circle"></i> سليمة</h3>
                        @else
                            <h3 class="text-danger"><i class="feather-x-circle"></i> غير سليمة</h3>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <h5 class="text-muted">إجمالي الملفات</h5>
                        <h3>{{ $totalFiles }}</h3>
                    </div>
                    <div class="col-md-3">
                        <h5 class="text-muted">الملفات المنفذة</h5>
                        <h3>{{ $totalRan }}</h3>
                    </div>
                    <div class="col-md-3">
                        <h5 class="text-muted">ملفات مفقودة (Ghost)</h5>
                        <h3 class="{{ $ghostMigrations->isEmpty() ? 'text-success' : 'text-danger' }}">{{ $ghostMigrations->count() }}</h3>
                    </div>
                    <div class="col-md-2">
                        <h5 class="text-muted">جداول مفقودة</h5>
                        <h3 class="{{ $missingTables->isEmpty() ? 'text-success' : 'text-danger' }}">{{ $missingTables->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="col-12">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="col-12">
            <div class="alert alert-danger">{{ session('error') }}</div>
        </div>
    @endif

    @if(!$ghostMigrations->isEmpty())
    <div class="col-12 mb-4">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h4 class="card-title mb-0">
                    <i class="feather-alert-triangle mr-1"></i> ملفات تهجير مفقودة من المجلد (Ghost Migrations)
                </h4>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <strong>تنبيه!</strong> السجلات التالية موجودة في قاعدة البيانات ولكن ملفات التهجير الخاصة بها غير موجودة في المجلد <code>database/migrations</code>. قد يحدث هذا إذا تم حذف الملفات يدوياً أو التراجع عن تحديث بشكل غير صحيح.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>اسم الملف المفقود</th>
                                <th style="width: 150px;">إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ghostMigrations as $ghost)
                                <tr>
                                    <td><code class="text-danger">{{ $ghost }}</code></td>
                                    <td>
                                        <form action="{{ route('admin.migration-manager.delete-ghost') }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل من قاعدة البيانات؟');">
                                            @csrf
                                            <input type="hidden" name="migration" value="{{ $ghost }}">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="feather-trash-2 mr-1"></i> حذف السجل
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(!$missingTables->isEmpty())
    <div class="col-12 mb-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="card-title text-white mb-0">
                    <i class="feather-alert-circle mr-1"></i> جداول مفقودة (Missing Tables)
                </h4>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <strong>خطر!</strong> الجداول التالية كان من المفترض أن يتم إنشاؤها عبر ملفات التهجير، لكنها غير موجودة فعلياً في قاعدة البيانات. هذا يعني أن البناء الحالي لقاعدة البيانات لا يتطابق مع ملفات التهجير.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>اسم الجدول المفقود</th>
                                <th>ملف التهجير المسؤول عنه</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($missingTables as $missing)
                                <tr>
                                    <td><strong class="text-danger">{{ $missing['table'] }}</strong></td>
                                    <td><code>{{ $missing['migration'] }}</code></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">الملفات التي لم يتم تنفيذها (Pending Migrations)</h4>
            </div>
            <div class="card-body">
                @if($pendingMigrations->isEmpty())
                    <div class="alert alert-info mb-0">لا يوجد ملفات تهجير (migrations) بانتظار التنفيذ. جميع الملفات منجزة.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>اسم الملف</th>
                                    <th style="width: 150px;">إجراء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingMigrations as $migration)
                                    <tr>
                                        <td>{{ $migration }}</td>
                                        <td>
                                            <form action="{{ route('admin.migration-manager.run') }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من تنفيذ ملف التهجير هذا؟');">
                                                @csrf
                                                <input type="hidden" name="migration" value="{{ $migration }}">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="feather-play mr-1"></i> تنفيذ
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
