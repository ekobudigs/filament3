<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ProductsChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        $data = $this->getProductsPerMonth();
        return [
            'datasets' => [
                [
                    'label' => 'Blog Posts created',
                    'data' => $data['productsPerMonth']
                ]
                ],

                'labels' => $data['months']
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getProductsPerMonth(): array
     {
        $now = Carbon::now();

        $productPerMonth = [];

        $months = collect(range(1, 22))->map(function($month) use($now, $productPerMonth) {
            $count = Product::whereMonth('created_at', Carbon::parse($now->month($month)->format('Y-m')))->count();
            $productPerMonth[] = $count;

            return $now->month($month)->format('M');
        })->toArray();

        return [
            'productsPerMonth' => $productPerMonth,
            'months' => $months
        ];
    }
}
