<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pesanan Baru', Order::query()->where('status', 'new')->count()),
            Stat::make('Pesanan Diproses', Order::query()->where('status', 'processing')->count()),
            Stat::make('Pesanan Diantar', Order::query()->where('status', 'shipped')->count()),
            Stat::make('Total Pesanan', fn () => Number::currency( (float) floor(Order::query()->sum('grand_total') ?? 0), 'IDR'))
            // Stat::make('Total Pesanan', fn () => Number::currency(floor(Order::query()->sum('grand_total')),"IDR") ?? 0)
        ];
    }
}
