<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number as Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 5;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Informasi Pesanan')->schema([
                        Select::make('user_id')
                        ->label('Customer')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                        Select::make('payment_method')
                        ->options([
                            'transfer' => 'Transfer Bank',
                            'cod' => 'Cash on Delivery (COD)'
                        ])
                        ->required(),
                        Select::make('payment_status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Sudah Dibayar',
                            'failed' => 'Gagal dibayar'
                        ])
                        ->default('pending')
                        ->required(),

                        ToggleButtons::make('status')
                        ->options ([
                            'new' => 'Pesanan Baru',
                            'processing' => 'Sedang Diproses',
                            'shipped' => 'Dalam Pengantaran',
                            'delivered' => 'Diterima/Selesai',
                            'canceled' => 'Dibatalkan'
                        ])->colors([
                            'new' => 'info',
                            'processing' => 'warning',
                            'shipped' => 'success',
                            'delivered' => 'success',
                            'canceled' => 'danger'
                        ])->icons([
                            'new' => 'heroicon-m-sparkles',
                            'processing' => 'heroicon-m-arrow-path',
                            'shipped' => 'heroicon-m-truck',
                            'delivered' => 'heroicon-m-check-badge',
                            'canceled' => 'heroicon-m-x-circle'
                        ])
                        ->inline()
                        ->default('new')
                        ->required(),

                        Select::make('currency')
                        ->options([
                            'rp' => 'IDR',
                            'usd' => 'USD'
                        ]),

                        Select::make('shipping_method')
                        ->options([
                            'self' => 'Jemput Sendiri',
                            'our' => 'Kurir Kami',
                            'gosend' => 'Gosend Instant'
                        ])
                        ->required(),
                        
                        Textarea::make('notes')
                        ->columnSpanFull()
                    ])->columns(2),

                    Section::make('Order Items')->schema([
                        Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->distinct()
                            -> disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->columnSpan(4)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set) => $set('unit_amount', Product::find($state)?->price ?? 0 ))
                            ->afterStateUpdated(fn($state, Set $set) => $set('total_amount', Product::find($state)?->price ?? 0 )),
                            
                            TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->columnSpan(2)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get)=> $set('total_amount', $state * $get('unit_amount'))),

                            TextInput::make('unit_amount')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(3),

                            TextInput::make('total_amount')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(3),

                        ])->columns(12),

                        Placeholder::make('grand_total_placeholder')
                            ->label('Grand Total')
                            ->content(function(Get $get, Set $set){
                                $total=0;
                                if(!$repeaters = $get('items')){
                                    return $total;
                                }

                                foreach($repeaters as $key => $repeater){
                                    $total += $get("items.{$key}.total_amount");
                                }
                                $set('grand_total', $total);
                                return Number::currency($total, 'IDR');
                            }),
                            Hidden::make('grand_total')
                            ->default(0),
                    ])

                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('grand_total')
                ->numeric()
                ->sortable()
                ->money('Rp. '),

                TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->formatStateUsing(fn ($state) => ucfirst($state))

                    ->searchable()
                    ->sortable(),
                SelectColumn::make('payment_status')
                ->label('Status Pembayaran')
                ->options([
                    'pending' => 'Pending',
                    'paid' => 'Sudah Dibayar',
                    'failed' => 'Gagal dibayar'
                ])
                ->searchable()
                ->sortable(),

                SelectColumn::make('status')
                ->label('Status Pemesanan')
                ->options([
                    'new' => 'Pesanan Baru',
                    'processing' => 'Sedang Diproses',
                    'shipped' => 'Dalam Pengantaran',
                    'delivered' => 'Diterima/Selesai',
                    'canceled' => 'Dibatalkan'
                ])
                ->searchable()
                ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault:true)
                    ->sortable(),

                TextColumn::make('updated_at')
                ->dateTime()
                ->toggleable(isToggledHiddenByDefault:true)
                ->sortable(),
               
                   
                    
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AddressRelationManager::class
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();

    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
