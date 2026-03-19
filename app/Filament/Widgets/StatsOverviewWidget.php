<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Students', User::where('role', 'student')->count())
                ->description('Registered students')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            Stat::make('Total Courses', Course::where('is_published', true)->count())
                ->description('Published courses')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('info'),
            Stat::make('Total Revenue', 'EGP ' . number_format(Order::where('status', 'completed')->sum('total'), 2))
                ->description('From completed orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
            Stat::make('Active Subscriptions', Subscription::where('status', 'active')->count())
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('primary'),
        ];
    }
}
