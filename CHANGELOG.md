# Changelog — MyAds Migration Manager

All notable changes to the **Migration Manager** plugin will be documented in this file.

## [1.1.0] - 2026-11-05
### Added / Enhanced
- **MYADS v4.7.0 Platform Compatibility**: Tested and verified discovery of migrations across core and plugin directories under Laravel 12.
- **Max Version Bump**: Updated `max_myads` requirement to `4.7.x`.
- **Navigation Integration**: Registered `settings_url` to `/admin/migration-manager`.

## [1.0.0] - 2026-06-15
### Added
- Initial release.
- Detection of pending migrations across core `database/migrations` and `plugins/*/database/migrations`.
- Detection and cleanup of ghost database records.
- Missing table structural verification.
- Duralux admin dashboard integration.
