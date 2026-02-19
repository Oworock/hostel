<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentBookingHistory extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Booking History';

    protected static string $view = 'filament.pages.student-booking-history';

    protected static ?string $slug = 'booking-history';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user() && auth()->user()->role === 'student';
    }

    public function mount(): void
    {
        abort_if(auth()->user()?->role !== 'student', 403);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->where('user_id', auth()->id())
                    ->with(['room', 'bed', 'payments'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('room.hostel.name')
                    ->label('Hostel')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room Number')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('bed.bed_number')
                    ->label('Bed Number')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('check_in_date')
                    ->label('Check In')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('check_out_date')
                    ->label('Check Out')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => formatCurrency((float) $state))
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'completed' => 'info',
                        'cancelled' => 'gray',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payments.status')
                    ->label('Payment Status')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state[0] ?? 'N/A') : 'N/A'),
            ])
            ->actions([
                Tables\Actions\Action::make('viewReceipt')
                    ->label('View Receipt')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Booking $record): string => route('student.bookings.receipt', $record))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
