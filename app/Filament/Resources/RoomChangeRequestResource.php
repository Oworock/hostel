<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomChangeRequestResource\Pages;
use App\Models\RoomChangeRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoomChangeRequestResource extends Resource
{
    protected static ?string $model = RoomChangeRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')->relationship('student', 'name')->disabled(),
                Forms\Components\Select::make('current_booking_id')->relationship('currentBooking', 'id')->disabled(),
                Forms\Components\Select::make('current_room_id')->relationship('currentRoom', 'room_number')->disabled(),
                Forms\Components\Select::make('requested_room_id')->relationship('requestedRoom', 'room_number')->disabled(),
                Forms\Components\Select::make('requested_bed_id')->relationship('requestedBed', 'bed_number')->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending_manager_approval' => 'Pending Manager Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->disabled(),
                Forms\Components\Textarea::make('reason')->disabled()->columnSpanFull(),
                Forms\Components\Textarea::make('manager_note')->disabled()->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')->label('Student')->searchable(),
                Tables\Columns\TextColumn::make('currentRoom.room_number')->label('Current Room')->searchable(),
                Tables\Columns\TextColumn::make('requestedRoom.room_number')->label('Requested Room')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('managerApprover.name')->label('Manager')->placeholder('-'),
                Tables\Columns\TextColumn::make('manager_note')->label('Manager Note')->limit(45),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_manager_approval' => 'Pending Manager Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoomChangeRequests::route('/'),
            'edit' => Pages\EditRoomChangeRequest::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'student',
            'currentRoom.hostel',
            'requestedRoom.hostel',
            'managerApprover',
        ]);
    }
}

