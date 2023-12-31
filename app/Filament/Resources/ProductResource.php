<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\RelationManagers\ProductImagesRelationManager;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';
    protected static ?string $navigationGroup = 'Products';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'title')
                    ->required()
                    ->columnSpan(3)
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(debounce: 1000)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                    ]),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)->columnSpan(3),
                Forms\Components\TextInput::make('mp')
                    ->required()
                    ->numeric()
                    ->live(debounce: '1000')
                    ->afterStateUpdated(fn (Set $set, ?int $state) => $set('sp', $state))
                    ->maxLength(255)
                    ->prefix('Rs')
                    ->columnSpan(2),
                Forms\Components\TextInput::make('discount')
                    ->maxLength(255)
                    ->afterStateUpdated(fn (Set $set, ?int $state, callable $get) => $set('sp', $get('mp') - ($get('mp') * $state / 100) ?? null))
                    ->columnSpan(2)
                    ->live(debounce: '1000')
                    ->numeric()
                    ->label('Discount Percentage')
                    ->suffix('%'),
                Forms\Components\TextInput::make('sp')
                    ->required()
                    ->maxLength(255)
                    ->readOnly()
                    ->prefix('Rs')
                    ->columnSpan(2),
                Forms\Components\RichEditor::make('description')
                    ->columnSpanFull(),
            ])->columns(6);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount')
                    ->searchable()
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            ProductImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
