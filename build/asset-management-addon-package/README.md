# Asset Management Addon

## Install options
1. Upload zip from Admin -> Addons.
2. Or unzip addon folder into one of these discovery locations:
- `addons/asset-management` (project root)
- `storage/app/private/addons/manual/asset-management`

Then open Admin -> Addons and click **Discover Folder Addons**.

## Activate / Deactivate
- Activate runs addon migrations and enables addon features.
- Deactivate removes addon migration tables (`assets`, `asset_issues`) and disables features.
