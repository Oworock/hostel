<?php

namespace App\Services;

use App\Models\Addon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class AddonLifecycleService
{
    public function activate(Addon $addon): void
    {
        if ($addon->slug === 'asset-management') {
            $this->runAssetManagementMigrations();
        }

        $addon->forceFill([
            'is_active' => true,
            'installed_at' => now(),
        ])->save();

        app(OutboundWebhookService::class)->dispatch('addon.activated', [
            'addon_id' => $addon->id,
            'slug' => $addon->slug,
            'name' => $addon->name,
            'version' => $addon->version,
        ]);
    }

    public function deactivate(Addon $addon): void
    {
        $addon->forceFill(['is_active' => false])->save();

        app(OutboundWebhookService::class)->dispatch('addon.deactivated', [
            'addon_id' => $addon->id,
            'slug' => $addon->slug,
            'name' => $addon->name,
            'version' => $addon->version,
        ]);
    }

    private function runAssetManagementMigrations(): void
    {
        $path = base_path('database/addons/asset-management/migrations');
        if (!is_dir($path)) {
            throw new RuntimeException('Asset management migration path is missing.');
        }

        Artisan::call('migrate', [
            '--path' => $path,
            '--realpath' => true,
            '--force' => true,
        ]);
    }

    public function purgeAddonData(Addon $addon): void
    {
        if ($addon->slug === 'asset-management') {
            $this->purgeAssetManagementData();
        }
    }

    private function purgeAssetManagementData(): void
    {
        $this->deleteAssetManagementImages();

        if (Schema::hasTable('asset_issues')) {
            Schema::drop('asset_issues');
        }

        if (Schema::hasTable('asset_subscription_notification_logs')) {
            Schema::drop('asset_subscription_notification_logs');
        }

        if (Schema::hasTable('asset_subscriptions')) {
            Schema::drop('asset_subscriptions');
        }

        if (Schema::hasTable('asset_movements')) {
            Schema::drop('asset_movements');
        }

        if (Schema::hasTable('assets')) {
            Schema::drop('assets');
        }

        DB::table('migrations')
            ->whereIn('migration', [
                '2026_02_19_000001_create_assets_table',
                '2026_02_19_000002_create_asset_issues_table',
                '2026_02_19_000003_add_extended_columns_to_assets_table',
                '2026_02_19_000004_create_asset_movements_table',
                '2026_02_19_000005_create_asset_subscriptions_table',
                '2026_02_19_000006_create_asset_subscription_notification_logs_table',
                '2026_02_19_000007_add_supplier_invoice_maintenance_to_assets_table',
            ])
            ->delete();
    }

    private function deleteAssetManagementImages(): void
    {
        if (!Schema::hasTable('assets')) {
            return;
        }

        DB::table('assets')
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->filter()
            ->each(fn ($path) => \Illuminate\Support\Facades\Storage::disk('public')->delete((string) $path));
    }
}
