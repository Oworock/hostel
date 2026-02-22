<?php

namespace App\Filament\Widgets;

use App\Models\Addon;
use App\Models\SalaryPayment;
use App\Models\StaffMember;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class StaffPayrollOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check()
            && auth()->user()?->isAdmin()
            && Addon::isActive('staff-payroll')
            && Schema::hasTable('staff_members');
    }

    protected function getStats(): array
    {
        $totalStaff = StaffMember::query()->count();
        $pendingApprovals = StaffMember::query()->where('status', 'pending')->count();
        $activeStaff = StaffMember::query()->where('status', 'active')->count();
        $suspendedStaff = StaffMember::query()->where('status', 'suspended')->count();

        $paidThisMonth = 0.0;
        if (Schema::hasTable('salary_payments')) {
            $paidThisMonth = (float) SalaryPayment::query()
                ->where('status', 'paid')
                ->where('payment_year', (int) now()->year)
                ->where('payment_month', (int) now()->month)
                ->sum('amount');
        }

        return [
            Stat::make(__('Staff Total'), (string) $totalStaff)
                ->description(__('All staff profiles'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make(__('Pending Approval'), (string) $pendingApprovals)
                ->description(__('Awaiting admin approval'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make(__('Active Staff'), (string) $activeStaff)
                ->description(__('Currently active'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make(__('Suspended'), (string) $suspendedStaff)
                ->description(__('Currently suspended'))
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('danger'),
            Stat::make(__('Paid This Month'), formatCurrency($paidThisMonth, compact: false))
                ->description(__('Payroll disbursed this month'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}

