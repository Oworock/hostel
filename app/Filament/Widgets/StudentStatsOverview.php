<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return [
                Stat::make('No Profile', 'Please create your profile')
                    ->color('danger'),
            ];
        }

        $myBookings = Booking::where('student_id', $student->id)->count();
        $activeBooking = Booking::where('student_id', $student->id)->where('status', 'active')->first();
        $completedBookings = Booking::where('student_id', $student->id)->where('status', 'completed')->count();
        $totalSpent = \App\Models\Payment::where('student_id', $student->id)->where('status', 'completed')->sum('amount');

        return [
            Stat::make('My Bookings', $myBookings)
                ->description('Total booking history')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),
            
            Stat::make('Current Status', $activeBooking ? 'Booked' : 'No Active Booking')
                ->description($activeBooking ? $activeBooking->room->name : 'Not currently booked')
                ->descriptionIcon($activeBooking ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($activeBooking ? 'success' : 'warning'),
            
            Stat::make('Completed Bookings', $completedBookings)
                ->description('Finished bookings')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('secondary'),
            
            Stat::make('Total Spent', '₦' . number_format($totalSpent, 2))
                ->description('On all bookings')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success'),
        ];
    }
}
