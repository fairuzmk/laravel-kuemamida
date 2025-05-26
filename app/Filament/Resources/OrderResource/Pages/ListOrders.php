<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array{
        return [
            OrderStats::class
        ];
    }
    public function getTabs(): array{
        return[
            null => Tab::make('All'),
            'new' => Tab::make('Pesanan Baru')->query(fn($query)=> $query->where('status', 'new')),
            'processing' => Tab::make('Pesanan Diproses')->query(fn($query)=> $query->where('status', 'processing')),
            'shipped' => Tab::make('Pesanan Dikirm')->query(fn($query)=> $query->where('status', 'shipped')),
            'delivered' => Tab::make('Pesanan Diterima')->query(fn($query)=> $query->where('status', 'delivered')),
            'canceled' => Tab::make('Pesanan Dibatalkan')->query(fn($query)=> $query->where('status', 'canceled')),
        ];
    }
}
