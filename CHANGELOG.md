# Changelog

## 1.0.5 - 2026-02-22

### Added
- Admin dashboard update checker notification.
- Dismissible update notice with incoming version number.
- Direct "Open System Updates" action from the update notice.

### Fixed
- System update workflow now shows explicit actions after package preview:
  - Continue Update
  - Decline Update
- Uploaded update flow now includes a dedicated "Preview Uploaded File" action.
- Update preview now lists only changed/new files (unchanged files are hidden).
- Student/Manager popup announcements now display reliably when active.
- Popup announcement visibility now re-triggers when admin updates an existing popup.
- File manager upload list now includes "Update File" (replace file) action.

### Improved
- Asset addon migrations now use MySQL-safe short index/constraint names.
- Student and manager sidebars restored to icon-based flat menu style.

