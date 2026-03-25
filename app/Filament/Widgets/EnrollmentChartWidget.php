<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use Filament\Widgets\ChartWidget;

class EnrollmentChartWidget extends ChartWidget
{
    protected ?string $heading = 'Enrollments';
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
                $data[] = Enrollment::whereDate('enrolled_at', $current)->count();
                $current->addDay();
            }
        } else {
            $current = $start->copy();
            while ($current->lte($end)) {
                $labels[] = $current->format('M Y');
                $data[] = Enrollment::whereYear('enrolled_at', $current->year)
                    ->whereMonth('enrolled_at', $current->month)
                    ->count();
                $current->addMonth();
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Enrollments',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
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
