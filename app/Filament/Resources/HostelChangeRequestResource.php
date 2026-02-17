<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HostelChangeRequestResource\Pages;
use App\Models\HostelChangeRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HostelChangeRequestResource extends Resource
{
    protected static ?string $model = HostelChangeRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')->relationship('student', 'name')->disabled(),
                Forms\Components\Select::make('current_hostel_id')->relationship('currentHostel', 'name')->disabled(),
                Forms\Components\Select::make('requested_hostel_id')->relationship('requestedHostel', 'name')->disabled(),
                Forms\Components\Textarea::make('reason')->columnSpanFull()->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending_manager_approval' => 'Pending Manager Approval',
                        'pending_admin_approval' => 'Pending Admin Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->disabled(),
                Forms\Components\Textarea::make('manager_note')->disabled(),
                Forms\Components\Textarea::make('admin_note')->label('Admin Note'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')->label('Student')->searchable(),
                Tables\Columns\TextColumn::make('currentHostel.name')->label('Current Hostel')->placeholder('Not Assigned'),
                Tables\Columns\TextColumn::make('requestedHostel.name')->label('Requested Hostel')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending_admin_approval' => 'info',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('manager_note')
                    ->label('Manager Note')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_manager_approval' => 'Pending Manager Approval',
                        'pending_admin_approval' => 'Pending Admin Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (HostelChangeRequest $record) => $record->status === 'pending_admin_approval')
                    ->action(function (HostelChangeRequest $record) {
                        $record->update([
                            'status' => 'approved',
                            'admin_approved_by' => auth()->id(),
                            'admin_approved_at' => now(),
                        ]);

                        $record->student?->update(['hostel_id' => $record->requested_hostel_id]);
                        app(\App\Services\HostelChangeNotificationService::class)
                            ->adminApproved($record->fresh(['student', 'requestedHostel.managers', 'currentHostel']), auth()->user());
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (HostelChangeRequest $record) => in_array($record->status, ['pending_admin_approval', 'pending_manager_approval'], true))
                    ->action(function (HostelChangeRequest $record) {
                        $record->update([
                            'status' => 'rejected',
                            'admin_approved_by' => auth()->id(),
                            'admin_approved_at' => now(),
                        ]);
                        app(\App\Services\HostelChangeNotificationService::class)
                            ->adminRejected($record->fresh(['student', 'requestedHostel', 'currentHostel']), auth()->user());
                    })
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHostelChangeRequests::route('/'),
            'edit' => Pages\EditHostelChangeRequest::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['student', 'currentHostel', 'requestedHostel']);
    }
}
