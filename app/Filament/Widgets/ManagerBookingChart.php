<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Hostel;
use Filament\Widgets\ChartWidget;

class ManagerBookingChart extends ChartWidget
{
    protected static ?string $heading = 'Your Bookings';

    protected function getData(): array
    {
        $manager = auth()->user();
        $hostels = Hostel::where('owner_id', $manager->id)->pluck('id');
        
        $data = Booking::whereIn('hostel_id', $hostels)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Bookings by Status',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                    ],
                ],
            ],
            'labels' => $data->pluck('status')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
