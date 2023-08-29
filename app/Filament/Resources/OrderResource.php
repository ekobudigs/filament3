<?php

namespace App\Filament\Resources;

use App\Enums\OrderstatusEnum;
use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Product;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Shop';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Order Details')->schema([
                        TextInput::make('number')->default('OR-' . random_int(100000, 999999))->disabled()->dehydrated()->required(),
                        Select::make('customer_id')->relationship('customers', 'name')->searchable()->required(),
                        Select::make('type')->options([
                            'pending' => OrderstatusEnum::PENDING->value,
                            'processing' => OrderstatusEnum::PROCESSING->value,
                            'completed' => OrderstatusEnum::PENDING->value,
                            'declined' => OrderstatusEnum::DECLINED->value,
                        ])->columnSpanFull()->required(),
                        MarkdownEditor::make('notes')->columnSpanFull(),
                    ])->columns(2),

                    Step::make('Order Items')->schema([
                        Repeater::make('items')->relationship()->schema([
                            Select::make('product_id')->label('Product')->options(Product::query()->pluck('name', 'id')),
                            TextInput::make('quantity')->numeric()->default(1)->required(),
                            TextInput::make('unit_price')->label('Unit Price')->disabled()->dehydrated()->numeric()->required()
                        ])->columns(3)
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
                TextColumn::make('total_price')->searchable()->sortable()->summarize([
                    Sum::make()->money()
                ]),
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
