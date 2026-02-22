<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\BedImage;
use App\Models\Hostel;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\SystemSetting;
use App\Models\UploadedFile as ManagedUploadedFile;
use App\Models\WelcomeSection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FileManagerController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = ManagedUploadedFile::query()
            ->where('uploader_id', $user->id)
            ->latest();

        $files = $query->paginate(20);
        $systemImages = collect();
        if ($user->isAdmin()) {
            $systemImages = $this->collectSystemImages();
        }

        return view('files.index', compact('files', 'systemImages'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isManager())) {
            abort(403);
        }

        $data = $request->validate([
            'file' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $file = $data['file'];
        $path = $file->store('managed-uploads/' . now()->format('Y/m'), 'local');

        ManagedUploadedFile::create([
            'uploader_id' => auth()->id(),
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('success', 'File uploaded successfully.');
    }

    public function show(ManagedUploadedFile $uploadedFile): Response
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && $uploadedFile->uploader_id !== $user->id)) {
            abort(403, 'Unauthorized file access.');
        }

        if (!Storage::disk($uploadedFile->disk)->exists($uploadedFile->path)) {
            abort(404);
        }

        return Storage::disk($uploadedFile->disk)->response(
            $uploadedFile->path,
            $uploadedFile->original_name,
            [
                'Content-Type' => $uploadedFile->mime_type ?? 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=300',
            ]
        );
    }

    public function destroy(ManagedUploadedFile $uploadedFile)
    {
        $user = auth()->user();

        if (!$user || (!$user->isAdmin() && $uploadedFile->uploader_id !== $user->id)) {
            abort(403, 'You can only delete files you uploaded.');
        }

        Storage::disk($uploadedFile->disk)->delete($uploadedFile->path);
        $uploadedFile->delete();

        return back()->with('success', 'File deleted successfully.');
    }

    public function update(Request $request, ManagedUploadedFile $uploadedFile)
    {
        $user = auth()->user();

        if (!$user || (!$user->isAdmin() && $uploadedFile->uploader_id !== $user->id)) {
            abort(403, 'You can only update files you uploaded.');
        }

        $data = $request->validate([
            'file' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $newFile = $data['file'];
        $newPath = $newFile->store('managed-uploads/' . now()->format('Y/m'), 'local');

        Storage::disk($uploadedFile->disk)->delete($uploadedFile->path);

        $uploadedFile->update([
            'disk' => 'local',
            'path' => $newPath,
            'original_name' => $newFile->getClientOriginalName(),
            'mime_type' => $newFile->getClientMimeType(),
            'size' => $newFile->getSize(),
        ]);

        return back()->with('success', 'File updated successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isManager())) {
            abort(403);
        }

        $data = $request->validate([
            'action' => ['required', 'in:delete'],
            'file_ids' => ['required', 'array', 'min:1'],
            'file_ids.*' => ['integer', 'exists:uploaded_files,id'],
        ]);

        $query = ManagedUploadedFile::query()
            ->whereIn('id', array_map('intval', $data['file_ids']));

        if (!$user->isAdmin()) {
            $query->where('uploader_id', $user->id);
        }

        $deleted = 0;
        $query->get()->each(function (ManagedUploadedFile $file) use (&$deleted): void {
            Storage::disk($file->disk)->delete($file->path);
            $file->delete();
            $deleted++;
        });

        return back()->with('success', $deleted > 0 ? ($deleted . ' file(s) deleted successfully.') : 'No files deleted.');
    }

    public function destroySystemImage(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Only admin can manage system images.');
        }

        $data = $request->validate([
            'source' => ['required', 'string'],
            'record_id' => ['nullable', 'integer'],
            'key' => ['nullable', 'string', 'max:255'],
            'path' => ['required', 'string', 'max:1024'],
            'disk' => ['required', 'in:public,local'],
        ]);

        $source = (string) $data['source'];
        $disk = (string) $data['disk'];
        $path = ltrim((string) $data['path'], '/');

        if (str_contains($path, '..')) {
            abort(422, 'Unsafe file path.');
        }

        Storage::disk($disk)->delete($path);

        switch ($source) {
            case 'hostel':
                if (!empty($data['record_id'])) {
                    Hostel::whereKey($data['record_id'])->update(['image_path' => null]);
                }
                break;
            case 'room_cover':
                if (!empty($data['record_id'])) {
                    Room::whereKey($data['record_id'])->update(['cover_image' => null]);
                }
                break;
            case 'room_image':
                if (!empty($data['record_id'])) {
                    RoomImage::whereKey($data['record_id'])->delete();
                }
                break;
            case 'bed_image':
                if (!empty($data['record_id'])) {
                    BedImage::whereKey($data['record_id'])->delete();
                }
                break;
            case 'welcome_section':
                if (!empty($data['record_id'])) {
                    WelcomeSection::whereKey($data['record_id'])->update(['image_path' => null]);
                }
                break;
            case 'asset':
                if (!empty($data['record_id']) && Schema::hasTable('assets')) {
                    Asset::whereKey($data['record_id'])->update(['image_path' => null]);
                }
                break;
            case 'system_setting':
                if (!empty($data['key'])) {
                    SystemSetting::where('key', $data['key'])->update(['value' => '']);
                }
                break;
            case 'managed_upload':
                if (!empty($data['record_id'])) {
                    ManagedUploadedFile::whereKey($data['record_id'])->delete();
                }
                break;
        }

        return back()->with('success', 'System image removed successfully.');
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

        ManagedUploadedFile::query()->latest()->limit(200)->get()->each(function ($row) use ($add): void {
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
