<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressRelationManager extends RelationManager
{
    protected static string $relationship = 'address';

    public function form(Form $form): Form
    {
        return $form

            ->schema([
                TextInput::make('first_name')
                ->required()
                ->maxLength(255),
                TextInput::make('last_name')
                ->required()
                ->maxLength(255),
                TextInput::make('phone')
                ->label('Nomor HP')
                ->required()
                ->tel()
                ->maxLength(20),
                TextInput::make('city')
                ->label('Kota')
                ->required()
                ->maxLength(255),
                TextInput::make('province')
                ->label('Provinsi')
                ->required()
                ->maxLength(255),
                TextInput::make('zip_code')
                ->label('Kodepos')
                ->numeric()
                ->maxLength(10),
                Textarea::make('street_address')
                    ->label('Alamat Lengkap')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('street_address')
            ->columns([
                Tables\Columns\TextColumn::make('fullname')
                ->label('Nama Penerima'),
                TextColumn::make ('phone')
                ->label('Nomor HP'),
                TextColumn::make ('street_address')
                ->label('Alamat Penerima'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
