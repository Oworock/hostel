<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportStudents')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    return Excel::download(new StudentsExport(), 'students-export.xlsx');
                }),
            Actions\Action::make('importStudents')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('sheet')
                        ->label('Student Excel File')
                        ->required()
                        ->disk('local')
                        ->directory('imports/students')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ]),
                ])
                ->action(function (array $data): void {
                    $path = (string) ($data['sheet'] ?? '');
                    if ($path === '') {
                        Notification::make()
                            ->danger()
                            ->title('No file selected')
                            ->send();
                        return;
                    }

                    try {
                        $import = new StudentsImport();
                        Excel::import($import, $path, 'local');
                    } catch (\Throwable $e) {
                        Storage::disk('local')->delete($path);
                        Notification::make()
                            ->danger()
                            ->title('Import failed')
                            ->body($e->getMessage())
                            ->send();
                        return;
                    }

                    Storage::disk('local')->delete($path);

                    Notification::make()
                        ->success()
                        ->title('Import completed')
                        ->body("Created: {$import->created}, Updated: {$import->updated}, Skipped: {$import->skipped}")
                        ->send();
                }),
        ];
    }
}
