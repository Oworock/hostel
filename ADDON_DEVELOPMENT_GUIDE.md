# Addon Development Guide

This document explains how addons are structured in this system, how they are loaded, and how developers can build or improve addons safely.

## 1. Addon Architecture (Current System)

The addon system is built around these components:

- `app/Models/Addon.php`
  - Stores addon metadata (`name`, `slug`, `version`, `manifest`, `is_active`, package/extracted paths).
  - `Addon::isActive('slug')` is the global runtime gate.

- `app/Services/AddonDiscoveryService.php`
  - Discovers addons from:
    - `addons/*/addon.json` (built-in)
    - `storage/app/.../addons/**/addon.json` (uploaded/extracted)
  - Upserts addon records in DB.

- `app/Services/AddonPackageService.php`
  - Upload package handler.
  - Validates ZIP and requires `addon.json` at ZIP root.
  - Extracts package safely to local storage.

- `app/Services/AddonLifecycleService.php`
  - Contains activation/deactivation/purge logic.
  - Currently has explicit lifecycle behavior for `asset-management`.

- `app/Filament/Resources/AddonResource.php`
  - Admin UI for upload, activate, deactivate, delete.

- `app/Http/Middleware/EnsureAddonActive.php`
  - Route middleware: `addon.active:<slug>`.
  - Returns `404` when addon is inactive.

## 2. Required Addon Package Structure

Your uploadable ZIP must include `addon.json` at archive root.

Example:

```text
my-addon.zip
├── addon.json
├── README.md
├── database/
│   └── addons/
│       └── my-addon/
│           └── migrations/
│               └── ...
└── Documentation/
    ├── index.html
    └── styles.css
```

### Minimal `addon.json`

```json
{
  "name": "My Addon",
  "slug": "my-addon",
  "version": "1.0.0",
  "description": "What this addon does"
}
```

Recommended fields:

- `name`
- `slug` (kebab-case)
- `version`
- `description`
- `requires` (php/laravel constraints)
- `features` (list of capabilities)

## 3. Where Addon Code Should Live

In this system, addon runtime code is currently inside the main app, while package ZIP provides metadata/migrations/docs.

Use this pattern for addon-specific runtime files:

- Models: `app/Models/*`
- Services: `app/Services/*`
- Controllers: `app/Http/Controllers/*`
- Filament resources/pages/widgets: `app/Filament/*`
- Views: `resources/views/*`
- Routes: `routes/web.php`, `routes/api.php`, `routes/console.php`
- Migrations: `database/addons/<slug>/migrations/*`

## 4. Activation Pattern

Use `AddonLifecycleService` to control what happens when addon is activated/deactivated/deleted.

Current example (`asset-management`):

- On activate:
  - Run addon migrations from:
    - `database/addons/asset-management/migrations`
- On deactivate:
  - Disable functionality, keep data.
- On delete:
  - Purge addon data/tables/images as implemented.

For new addon slugs, add explicit logic:

- migration runner
- optional seeders
- optional cleanup/purge behavior

## 5. Runtime Gating (Important)

Always protect addon functionality behind active checks.

Use one or more:

1. Route middleware:
   - `->middleware('addon.active:my-addon')`
2. Resource visibility:
   - `Addon::isActive('my-addon') && Schema::hasTable(...)`
3. Widget/page `canView()` checks.
4. Service-level guards before executing addon logic.

This ensures system works normally when addon is not active.

## 6. DB Safety Rules

- Keep addon migrations in `database/addons/<slug>/migrations`.
- Use addon-specific tables/prefixing names when possible.
- Check `Schema::hasTable(...)` before querying optional addon tables.
- If deactivation should keep data, do not drop tables in deactivate flow.
- If delete should remove data, do it only in explicit delete/purge flow.

## 7. UI Integration Guidelines

For Filament integration:

- Add navigation only when addon is active.
- Add explicit `canViewAny/canCreate/canEdit/canDelete` checks.
- Keep admin/manager/student visibility separated by role.

For Blade/regular routes:

- Add conditional menu links only when addon is active.
- Use middleware on addon routes.

## 8. API + Webhook Integration

If addon introduces entities, also update:

- API endpoints (`routes/api.php` + controller methods).
- API documentation page (`ApiDocumentationController`).
- Outgoing webhook event list and payloads.

Use event names like:

- `my_addon.entity.created`
- `my_addon.entity.updated`
- `my_addon.entity.deleted`

## 9. Notifications Pattern

Use database notifications for user-facing addon events:

- `App\Notifications\SystemEventNotification`

Keep payload minimal but useful:

- entity id
- actor id
- related hostel/room/user ids
- status/action

## 10. Developer Checklist for New Addon

1. Define slug and create `addon.json`.
2. Add addon migrations under `database/addons/<slug>/migrations`.
3. Add lifecycle hooks in `AddonLifecycleService`.
4. Implement runtime models/services/controllers/resources.
5. Gate routes/resources/widgets by addon active state.
6. Add role-based permission checks.
7. Update API + webhook + docs if needed.
8. Add test/demo documentation in `Documentation/`.
9. Create upload ZIP with `addon.json` at root.
10. Test: upload -> activate -> use -> deactivate -> reactivate -> delete.

## 11. Common Mistakes to Avoid

- Missing `addon.json` at ZIP root.
- Using addon tables without `Schema::hasTable` checks.
- Showing menu/resources when addon inactive.
- Putting destructive cleanup in deactivation instead of delete.
- Hard-coding behavior without slug checks in lifecycle service.

## 12. Recommended Future Improvement (Optional)

To make addons more self-contained, adopt per-addon manifest fields like:

- `migrations_path`
- `providers`
- `routes`
- `permissions`
- `cleanup` policy

Then `AddonLifecycleService` can become generic instead of slug-specific.

---

If you are building a new addon now, use `asset-management` as reference implementation and keep all new functionality behind `Addon::isActive('<your-slug>')` checks.
