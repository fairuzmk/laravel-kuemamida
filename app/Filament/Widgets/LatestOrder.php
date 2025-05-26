<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;

use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrder extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort=2;
    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                ->label('Order ID'),
                TextColumn::make('user.name')
                ->label('Order ID')
                ->searchable(),
                TextColumn::make('grand_total')
                ->label('Total Pesanan'),
                TextColumn::make('status')
                ->badge()
                ->color(fn(string $state):string => match($state){
                    'new' => 'info',
                    'processing' => 'warning',
                    'shipped' => 'success',
                    'delivered' => 'success',
                    'canceled' => 'danger'
                })
                ->icon(fn(string $state):string => match($state){
                    'new' => 'heroicon-m-spark',
                    'processing' => 'heroicon-m-arrow-path',
                    'shipped' => 'heroicon-m-truck',
                    'delivered' => 'heroicon-m-check-badge',
                    'canceled' => 'heroicon-m-x-circle'
                })
                ->label('Status')
                ->sortable(),
                
                TextColumn::make('payment_method')
                ->label('Metode Pembayaran')
                ->sortable(),

                TextColumn::make('payment_status')
                ->label('Status Pembayaran')
                ->sortable()
                ->badge(),
                TextColumn::make('created_at')
                ->label('Tanggal Pemesanan')
                ->dateTime()
            ])

            ->actions([
                Action::make('View Order')
                 ->url(fn(Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                 ->icon('heroicon-m-eye'),
             ]);
    }
}
