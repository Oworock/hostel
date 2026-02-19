<?php

namespace App\Filament\Pages;

use App\Models\Asset;
use App\Models\BedImage;
use App\Models\Hostel;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\SystemSetting;
use App\Models\UploadedFile;
use App\Models\WelcomeSection;
use Filament\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FileManagerPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 7;

    protected static ?string $title = 'File Manager';

    protected static ?string $slug = 'system/file-manager';

    protected static string $view = 'filament.pages.file-manager-page';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getViewData(): array
    {
        $files = UploadedFile::query()
            ->where('uploader_id', auth()->id())
            ->latest()
            ->paginate(20, ['*'], 'files_page');

        $systemImages = $this->collectSystemImages();
        $imagesPerPage = 20;
        $imagesPage = max((int) request()->query('images_page', 1), 1);
        $imagesTotal = $systemImages->count();
        $imagesItems = $systemImages->slice(($imagesPage - 1) * $imagesPerPage, $imagesPerPage)->values();
        $systemImagesPaginator = new LengthAwarePaginator(
            $imagesItems,
            $imagesTotal,
            $imagesPerPage,
            $imagesPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => 'images_page',
            ]
        );

        return [
            'files' => $files,
            'systemImages' => $systemImagesPaginator,
        ];
    }

    private function collectSystemImages(): Collection
    {
        $images = collect();

        $add = function (
            string $source,
            string $disk,
            ?string $path,
            ?string $label = null,
            ?int $recordId = null,
            ?string $settingKey = null,
            ?string $uploadedAt = null
        ) use (&$images): void {
            $path = $path ? ltrim($path, '/') : '';
            if ($path === '' || str_contains($path, '..')) {
                return;
            }

            $exists = Storage::disk($disk)->exists($path);
            $size = $exists ? Storage::disk($disk)->size($path) : null;

            $images->push([
                'source' => $source,
                'disk' => $disk,
                'path' => $path,
                'label' => $label ?: basename($path),
                'record_id' => $recordId,
                'setting_key' => $settingKey,
                'uploaded_at' => $uploadedAt,
                'size' => $size,
                'exists' => $exists,
            ]);
        };

        Hostel::query()->whereNotNull('image_path')->get(['id', 'name', 'image_path', 'updated_at'])->each(
            fn ($row) => $add('hostel', 'public', $row->image_path, 'Hostel: ' . $row->name, $row->id, null, optional($row->updated_at)?->toDateTimeString())
        );

        Room::query()->whereNotNull('cover_image')->get(['id', 'room_number', 'cover_image', 'updated_at'])->each(
            fn ($row) => $add('room_cover', 'public', $row->cover_image, 'Room Cover: ' . $row->room_number, $row->id, null, optional($row->updated_at)?->toDateTimeString())
        );

        if (Schema::hasTable('room_images')) {
            RoomImage::query()->with('room:id,room_number')->get(['id', 'room_id', 'image_path', 'updated_at'])->each(
                fn ($row) => $add('room_image', 'public', $row->image_path, 'Room Image: ' . ($row->room?->room_number ?? ('Room #' . $row->room_id)), $row->id, null, optional($row->updated_at)?->toDateTimeString())
            );
        }

        if (Schema::hasTable('bed_images')) {
            BedImage::query()->get(['id', 'bed_id', 'image_path', 'updated_at'])->each(
                fn ($row) => $add('bed_image', 'public', $row->image_path, 'Bed Image: Bed #' . $row->bed_id, $row->id, null, optional($row->updated_at)?->toDateTimeString())
            );
        }

        WelcomeSection::query()->whereNotNull('image_path')->get(['id', 'title', 'image_path', 'updated_at'])->each(
            fn ($row) => $add('welcome_section', 'public', $row->image_path, 'Welcome Section: ' . $row->title, $row->id, null, optional($row->updated_at)?->toDateTimeString())
        );

        if (Schema::hasTable('assets')) {
            Asset::query()->whereNotNull('image_path')->get(['id', 'name', 'image_path', 'updated_at'])->each(
                fn ($row) => $add('asset', 'public', $row->image_path, 'Asset: ' . $row->name, $row->id, null, optional($row->updated_at)?->toDateTimeString())
            );
        }

        SystemSetting::query()
            ->whereIn('key', ['global_header_logo', 'global_header_logo_light', 'global_header_logo_dark', 'global_header_favicon', 'app_logo'])
            ->get(['key', 'value', 'updated_at'])
            ->each(fn ($row) => $add('system_setting', 'public', $row->value, 'System: ' . $row->key, null, $row->key, optional($row->updated_at)?->toDateTimeString()));

        UploadedFile::query()->latest()->limit(200)->get()->each(function ($row) use ($add): void {
            if (!str_starts_with((string) $row->mime_type, 'image/')) {
                return;
            }
            $add(
                'managed_upload',
                $row->disk ?: 'local',
                $row->path,
                'Managed Upload: ' . ($row->original_name ?: basename((string) $row->path)),
                $row->id,
                null,
                optional($row->created_at)?->toDateTimeString()
            );
        });

        return $images
            ->unique(fn (array $item) => $item['disk'] . '|' . $item['path'] . '|' . $item['source'] . '|' . ($item['record_id'] ?? ''))
            ->values();
    }
}
