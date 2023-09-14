<?php

namespace App\Filament\Widgets;

use App\Enums\OrderstatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    
    protected static ?string $pollingInterval = '15s';
    protected static bool $isLazy = true;
    protected function getStats(): array
    {
        return [
            Stat::make('Total Customers', Customer::count())
            ->description('Increase In Customers')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success')
            ->chart(['7', '3', '4', '5', '6', '3', '5', '3']),
            Stat::make('Total Product', Product::count())
            ->description('Total Product in app')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger')
            ->chart(['7', '3', '4', '5', '6', '3', '5', '3']),
            Stat::make('Pending Orders', Order::where('status', OrderstatusEnum::PENDING->value)->count())
            ->description('Total Product in app')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger')
            ->chart(['7', '3', '4', '5', '6', '3', '5', '3'])
        ];
    }
}
