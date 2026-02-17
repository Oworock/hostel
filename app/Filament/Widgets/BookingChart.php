<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BookingChart extends ChartWidget
{
    protected static ?string $heading = 'Bookings Over Time';
    protected static ?int $sort = 90;
    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    protected function getData(): array
    {
        $driver = config('database.default');
        
        if ($driver === 'sqlite') {
            $data = Booking::selectRaw("date(created_at) as date, COUNT(*) as count")
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
        } else {
            $data = Booking::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Bookings',
                    'data' => $data->pluck('count')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
