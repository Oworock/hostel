<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use App\Models\SystemUpdateAudit;
use App\Services\SystemUpdateService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemUpdatePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 9;

    protected static ?string $title = 'System Updates';

    protected static ?string $slug = 'system/updates';

    protected static string $view = 'filament.pages.system-update-page';

    private const MANIFEST_URL = 'https://oworock.com/hostel/manifest.json';

    public ?array $data = [];

    /** @var array<string, mixed>|null */
    public ?array $previewReport = null;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        abort_unless(static::shouldRegisterNavigation(), 403);

        $this->form->fill([
            'current_version' => (string) SystemSetting::getSetting('system_app_version', '1.0.0'),
            'update_zip_path' => null,
            'acknowledge_custom_warning' => false,
            'update_decision' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Version')
                    ->schema([
                        Forms\Components\TextInput::make('current_version')
                            ->label('Current Version')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Forms\Components\Section::make('Upload Update ZIP (Optional)')
                    ->schema([
                        Forms\Components\FileUpload::make('update_zip_path')
                            ->label('Update Zip File')
                            ->disk('local')
                            ->directory('updates')
                            ->acceptedFileTypes([
                                'application/zip',
                                'application/x-zip-compressed',
                            ])
                            ->helperText('Upload manual update zip if you are not using Pull Update.')
                            ->preserveFilenames(false),
                    ]),
                Forms\Components\Section::make('Update Decision')
                    ->visible(fn (): bool => !empty($this->previewReport))
                    ->schema([
                        Forms\Components\Placeholder::make('warning')
                            ->label('Caution')
                            ->content('If you customized this project, review affected files carefully before continuing.'),
                        Forms\Components\Radio::make('update_decision')
                            ->label('Decision')
                            ->options([
                                'continue' => 'Continue with update',
                                'decline' => 'Decline update',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('acknowledge_custom_warning')
                            ->label('I understand customized files may be overwritten')
                            ->default(false),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pullRemoteUpdate')
                ->label('Pull Update')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (): null => ($this->pullRemoteUpdate() ?? null)),
        ];
    }

    public function getViewData(): array
    {
        $auditRows = collect();
        try {
            if (Schema::hasTable('system_update_audits')) {
                $auditRows = SystemUpdateAudit::query()->latest('id')->limit(20)->get();
            }
        } catch (Throwable) {
            // Keep update page usable even when migration is pending.
        }

        return [
            'previewReport' => $this->previewReport,
            'auditRows' => $auditRows,
        ];
    }

    private function pullRemoteUpdate(): void
    {
        try {
            $service = app(SystemUpdateService::class);
            $manifest = $service->fetchRemoteManifest(self::MANIFEST_URL);
            $zipUrl = (string) ($manifest['zip_url'] ?? '');
            if ($zipUrl === '') {
                throw new \RuntimeException('Manifest does not provide zip_url.');
            }

            $localPath = $service->downloadRemotePackage($zipUrl);
            $this->data['update_zip_path'] = $localPath;
            $this->form->fill($this->data);

            $this->previewReport = $service->previewFromStoredPath($localPath);
            $this->previewReport['manifest'] = [
                'version' => $manifest['version'] ?? null,
                'notes' => $manifest['notes'] ?? null,
            ];

            Notification::make()
                ->success()
                ->title('Update package pulled')
                ->body('Preview is ready below. Choose Continue or Decline.')
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Failed to pull update')
                ->body($e->getMessage())
                ->send();
        }
    }

    private function previewUploadedUpdate(): void
    {
        $data = $this->form->getState();
        $zipPath = $this->normalizeStoredFileValue($data['update_zip_path'] ?? null);
        if ($zipPath === '') {
            Notification::make()
                ->warning()
                ->title('Update package required')
                ->body('Upload an update zip file first, or use Pull Update.')
                ->send();

            return;
        }

        try {
            $this->previewReport = app(SystemUpdateService::class)->previewFromStoredPath($zipPath);
            Notification::make()
                ->success()
                ->title('Update preview ready')
                ->body('Choose Continue or Decline in Update Decision.')
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Preview failed')
                ->body($e->getMessage())
                ->send();
        }
    }

    private function processDecision(): void
    {
        if ($this->previewReport === null) {
            Notification::make()->warning()->title('No preview to process')->send();

            return;
        }

        $data = $this->form->getState();
        $decision = (string) ($data['update_decision'] ?? '');
        if (!in_array($decision, ['continue', 'decline'], true)) {
            Notification::make()->warning()->title('Select Continue or Decline')->send();

            return;
        }

        $zipPath = $this->normalizeStoredFileValue($data['update_zip_path'] ?? null);
        $packageName = basename($zipPath);
        $version = Arr::get($this->previewReport, 'manifest.version');
        $filesTotal = (int) ($this->previewReport['files_total'] ?? 0);

        if ($decision === 'decline') {
            $this->writeAudit([
                'user_id' => auth()->id(),
                'action' => 'declined',
                'package_name' => $packageName !== '' ? $packageName : null,
                'package_path' => $zipPath !== '' ? $zipPath : null,
                'version' => is_string($version) && $version !== '' ? $version : null,
                'files_total' => $filesTotal,
                'files_applied' => 0,
                'details' => ['preview' => $this->previewReport],
                'applied_at' => now(),
            ]);

            $this->previewReport = null;
            $this->data['update_decision'] = null;
            $this->form->fill($this->data);
            Notification::make()->success()->title('Update declined')->send();

            return;
        }

        if (empty($data['acknowledge_custom_warning'])) {
            Notification::make()
                ->warning()
                ->title('Confirmation required')
                ->body('Acknowledge customization risk before continuing.')
                ->send();

            return;
        }

        try {
            $selected = array_map(
                static fn (array $row): string => (string) ($row['path'] ?? ''),
                (array) ($this->previewReport['affected'] ?? [])
            );
            $selected = array_values(array_filter($selected));

            $result = app(SystemUpdateService::class)->applyFromStoredPath($zipPath, $selected);
            $applied = (int) ($result['applied'] ?? 0);

            $this->writeAudit([
                'user_id' => auth()->id(),
                'action' => 'applied',
                'package_name' => $packageName !== '' ? $packageName : null,
                'package_path' => $zipPath !== '' ? $zipPath : null,
                'version' => is_string($version) && $version !== '' ? $version : null,
                'files_total' => $filesTotal,
                'files_applied' => $applied,
                'details' => ['preview' => $this->previewReport],
                'applied_at' => now(),
            ]);

            $this->previewReport = null;
            $this->data['update_decision'] = null;
            $this->form->fill($this->data);

            Notification::make()
                ->success()
                ->title('Update applied successfully')
                ->body("Files applied: {$applied}")
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Update failed')
                ->body($e->getMessage())
                ->send();
        }
    }

    private function normalizeStoredFileValue(mixed $value): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }
        if (is_object($value)) {
            return '';
        }

        return trim((string) ($value ?? ''));
    }

    public function updatedDataUpdateZipPath(mixed $state): void
    {
        if ($this->normalizeStoredFileValue($state) === '') {
            return;
        }

        $this->previewUploadedUpdate();
    }

    public function updatedDataUpdateDecision(mixed $state): void
    {
        if (!in_array((string) $state, ['continue', 'decline'], true)) {
            return;
        }

        $this->processDecision();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function writeAudit(array $payload): void
    {
        try {
            if (!Schema::hasTable('system_update_audits')) {
                return;
            }

            SystemUpdateAudit::create($payload);
        } catch (Throwable) {
            // Keep update flow working even when audit storage is unavailable.
        }
    }
}
