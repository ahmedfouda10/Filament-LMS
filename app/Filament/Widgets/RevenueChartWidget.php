<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue';
    public ?string $filter = 'week';

    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1;

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $start = null;
        $end = null;
        $perData = null;
        $data = [];
        $labels = [];

        switch ($this->filter) {
            case 'week':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                $perData = 'perDay';
                break;
            case 'month':
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                $perData = 'perDay';
                break;
            case 'year':
                $start = now()->startOfYear();
                $end = now()->endOfYear();
                $perData = 'perMonth';
                break;
        }


        if ($perData === 'perDay') {
            $current = $start->copy();
            while ($current->lte($end)) {
                $labels[] = $current->format('D d');
                $data[] = (float) Order::where('status', 'completed')
                    ->whereDate('paid_at', $current)
                    ->sum('total');
                $current->addDay();
            }
        } else {
            $current = $start->copy();
            while ($current->lte($end)) {
                $labels[] = $current->format('M Y');
                $data[] = (float) Order::where('status', 'completed')
                    ->whereYear('paid_at', $current->year)
                    ->whereMonth('paid_at', $current->month)
                    ->sum('total');
                $current->addMonth();
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (EGP)',
                    'data' => $data,
                    'borderColor' => '#236bba',
                    'backgroundColor' => 'rgba(35, 107, 186, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
