<?php

namespace App\Observers;

use App\Models\SalaryPayment;
use App\Services\StaffPayrollNotificationService;

class SalaryPaymentObserver
{
    public function created(SalaryPayment $salaryPayment): void
    {
        if ($salaryPayment->status === 'paid') {
            app(StaffPayrollNotificationService::class)->notifySalaryPaid($salaryPayment);
        }
    }

    public function updated(SalaryPayment $salaryPayment): void
    {
        if ($salaryPayment->wasChanged('status') && $salaryPayment->status === 'paid') {
            app(StaffPayrollNotificationService::class)->notifySalaryPaid($salaryPayment);
        }
    }
}

