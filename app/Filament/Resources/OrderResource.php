<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\OrderstatusEnum;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Wizard\Step;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\OrderResource\RelationManagers;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Shop';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', '=', 'processing')->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', '=', 'processing')->count() > 10 ? 'warning' : 'primary';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Order Details')->schema([
                        TextInput::make('number')->default('OR-' . random_int(100000, 999999))->disabled()->dehydrated()->required(),
                        Select::make('customer_id')->relationship('customer', 'name')->searchable()->required(),
                        TextInput::make('shipping_price')->label('Shipping Costs')->dehydrated()->numeric()->required(),
                        Select::make('type')->options([
                            'pending' => OrderstatusEnum::PENDING->value,
                            'processing' => OrderstatusEnum::PROCESSING->value,
                            'completed' => OrderstatusEnum::COMPLETED->value,
                            'declined' => OrderstatusEnum::DECLINED->value,
                        ])->required(),
                        MarkdownEditor::make('notes')->columnSpanFull(),
                    ])->columns(2),

                    Step::make('Order Items')->schema([
                        Repeater::make('items')->relationship()->schema([
                            Select::make('product_id')->label('Product')->options(Product::query()->pluck('name', 'id'))->required()->reactive()->afterStateUpdated(fn($state, Forms\Set $set) => 
                            $set('unit_price', Product::find($state)?->price ?? 0)),
                            TextInput::make('quantity')->numeric()->default(1)->required()->live()->dehydrated(),
                            TextInput::make('unit_price')->label('Unit Price')->disabled()->dehydrated()->numeric()->required(),

                            Placeholder::make('total_price')->label('Total Price')->content(function ($get) {
                                return $get('quantity') * $get('unit_price');
                            })
                        ])->columns(4)
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('customer.name')->searchable()->sortable()->toggleable(),
                TextColumn::make('status')->searchable()->sortable(),
                TextColumn::make('created_at')->label('Order Date')->date()
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    DeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
