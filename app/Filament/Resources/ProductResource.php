<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\ProductTypeEnum;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Products';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $recordTitleAttribute = 'name';
    // protected static ?string $activeNavigationIcon = 'heroicon-o-document-text';

    // public static function getNavigationBadge(): ?string
    // {
    //     return 'NEW';
    // }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }


    protected static int $globalSearchResultsLimit = 20;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Brand' => $record->brand->name,
            'Description' => $record->description
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['brand']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')->required()->live(onBlur: true)->unique()->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation !== 'create') {
                                return;
                            }

                            $set('slug', Str::slug($state));
                        }),
                        TextInput::make('slug')->disabled()->dehydrated()->required()->unique(Product::class, 'slug', ignoreRecord: true),
                        MarkdownEditor::make('description')->columnSpanFull()
                    ])->columns(2),
                    Section::make('Pricing & Inventory')->schema([
                        TextInput::make('sku')->label('SKU (Stock Keeping Unit)')->unique()->required(),
                        TextInput::make('price')->numeric()->required(),
                        TextInput::make('quantity')->numeric()->minValue(0)->maxValue(100)->required(),
                        Select::make('type')->options([
                            'downloadable' => ProductTypeEnum::DOWNLOADBLE->value,
                            'deliverable' => ProductTypeEnum::DELIVERABLE->value,
                        ])->required()
                    ])->columns(2),
                ]),
                Group::make()->schema([
                    Section::make('Status')->schema([
                        Toggle::make('is_visible')->label('Visibility')->helperText('Enable or disable product Visibility')->default(true),
                        Toggle::make('is_featured')->label('Featured')->helperText('Enable or disable product Featured Status'),
                        DatePicker::make('published_at')->label('Availability')->default(now())
                    ]),
                    Section::make('Image')->schema([
                        FileUpload::make('image')->directory('form-attachments')->preserveFilenames()->image()->imageEditor()
                    ])->collapsible(),
                    Section::make('Associations')->schema([
                        Select::make('brand_id')->relationship('brand', 'name')->required(),
                        Select::make('categories')->relationship('categories', 'name')->multiple()->required()
                    ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('brand.name')->searchable()->sortable()->toggleable(),
                IconColumn::make('is_visible')->sortable()->toggleable()->label('Visibility')->boolean(),
                TextColumn::make('price'),
                TextColumn::make('quantity')->sortable()->toggleable(),
                TextColumn::make('published_at')->date()->sortable(),
                TextColumn::make('type'),
            ])
            ->filters([
                TernaryFilter::make('is_visible')->label('Visibility')->boolean()->trueLabel('Only Visible Products')->falseLabel('Only Hidden Products')->native(false),
                SelectFilter::make('brand')->relationship('brand', 'name')
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
