# 🗄️ Migration Manager for MYADS — v1.0.0

[![MYADS Compatibility](https://img.shields.io/badge/MYADS-4.5.6%2B-blue.svg)](https://github.com/mrghozzi)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

**Migration Manager** is an essential database maintenance and health inspection plugin for the **MYADS** platform. It provides an intuitive GUI inside the admin panel to detect, audit, and execute pending migrations (both core and plugin-level) with single-click operations without requiring SSH or manual terminal access.

---

## ✨ Features

- **🔍 Database Integrity Inspector**:
  - Automatically identifies whether your database schema is healthy and synchronized.
  - Detects **Missing Tables** whose creation was declared in migration files but does not exist in the database.
- **⚡ Pending Migrations Runner**:
  - Discovers all pending migrations in `database/migrations` and inside individual plugins (`plugins/*/database/migrations`).
  - Allows executing migrations individually with real-time feedback.
- **👻 Ghost Migrations Cleaner**:
  - Spots orphaned migration entries recorded in the `migrations` table whose physical files no longer exist on disk.
  - Safe 1-click removal of ghost records to keep database logs clean.
- **🛡️ Secure Access**:
  - Protected behind standard MYADS authentication and admin permission middleware (`['web', 'auth', 'admin']`).
  - Integrated seamlessly into the admin sidebar navigation.

---

## 📋 Requirements

- **MYADS Platform**: `v4.5.6` or higher
- **PHP**: `8.2+`
- **Database**: MySQL 5.7+ / MariaDB 10.3+

---

## 🚀 Installation

1. Copy or extract the `migration-manager` folder into the `plugins/` directory of your MYADS installation:
   ```text
   plugins/
   └── migration-manager/
       ├── boot.php
       ├── plugin.json
       ├── thumbnail.png
       ├── views/
       └── README.md
   ```
2. Log in to your **MYADS Admin Dashboard**.
3. Navigate to **Plugins** and find **Migration Manager**.
4. Click **Activate**.
5. Access the manager directly from the sidebar menu under **إدارة التهجير (Migrations)** or browse to:
   ```text
   https://yourdomain.com/admin/migration-manager
   ```

---

## 📖 How It Works

| Feature | Description |
| :--- | :--- |
| **Health Check** | Displays total files, executed migrations, ghost records, and missing tables. |
| **Run Migration** | Executes `Artisan::call('migrate')` targeting only the specific migration file. |
| **Delete Ghost** | Removes legacy or deleted migration filenames from the `migrations` database table. |

---

## 👤 Author & Support

- **Author**: MrGhozzi
- **GitHub**: [@mrghozzi](https://github.com/mrghozzi)
- **Repository**: [https://github.com/mrghozzi/myads-migration-manager](https://github.com/mrghozzi/myads-migration-manager)

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).
