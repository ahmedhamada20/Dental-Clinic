<?php

namespace App\Modules\Reports\Services\Concerns;

use App\Modules\Reports\DTOs\ReportFilterDTO;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait InteractsWithReportDates
{
    protected function normalizeDateRange(ReportFilterDTO $dto): array
    {
        $from = $dto->fromDate ? Carbon::parse($dto->fromDate)->startOfDay() : now()->startOfMonth()->startOfDay();
        $to = $dto->toDate ? Carbon::parse($dto->toDate)->endOfDay() : now()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    protected function applyDateRange(Builder $query, string $column, ReportFilterDTO $dto): Builder
    {
        [$from, $to] = $this->normalizeDateRange($dto);

        return $query->whereBetween($column, [$from, $to]);
    }

    protected function buildTrend(Collection $rows, Carbon $from, Carbon $to, string $groupBy = 'day'): array
    {
        $groupBy = in_array($groupBy, ['day', 'week', 'month'], true) ? $groupBy : 'day';
        $period = [];
        $cursor = $from->copy();

        while ($cursor->lte($to)) {
            $key = $this->formatPeriodKey($cursor, $groupBy);
            $period[$key] = 0;
            $cursor = match ($groupBy) {
                'week' => $cursor->copy()->addWeek(),
                'month' => $cursor->copy()->addMonth(),
                default => $cursor->copy()->addDay(),
            };
        }

        foreach ($rows as $row) {
            $period[$row['period']] = (float) $row['value'];
        }

        return collect($period)
            ->map(fn ($value, $label) => ['label' => $label, 'value' => $value])
            ->values()
            ->all();
    }

    protected function trendExpression(string $column, string $groupBy = 'day'): string
    {
        return match ($groupBy) {
            'week' => "DATE_FORMAT(DATE_SUB({$column}, INTERVAL WEEKDAY({$column}) DAY), '%Y-%m-%d')",
            'month' => "DATE_FORMAT({$column}, '%Y-%m')",
            default => "DATE_FORMAT({$column}, '%Y-%m-%d')",
        };
    }

    protected function formatPeriodKey(Carbon $date, string $groupBy): string
    {
        return match ($groupBy) {
            'week' => $date->copy()->startOfWeek()->format('Y-m-d'),
            'month' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    protected function percentage(float|int $numerator, float|int $denominator): float
    {
        if ((float) $denominator === 0.0) {
            return 0.0;
        }

        return round(((float) $numerator / (float) $denominator) * 100, 2);
    }
}

