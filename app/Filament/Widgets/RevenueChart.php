<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Revenue';

    protected function getData(): array
    {
        $driver = config('database.default');
        
        if ($driver === 'sqlite') {
            $data = Payment::selectRaw("strftime('%m', created_at) as month, SUM(amount) as total")
                ->where('status', 'paid')
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();
        } else {
            $data = Payment::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                ->where('status', 'paid')
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();
        }

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₦)',
                    'data' => $data->pluck('total')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn($item) => $months[intval($item->month) - 1] ?? '')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
