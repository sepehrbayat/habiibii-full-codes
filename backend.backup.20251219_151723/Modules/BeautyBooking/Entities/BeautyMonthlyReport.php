<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Beauty Monthly Report Model
 * مدل گزارش ماهانه
 *
 * @property int $id
 * @property int $year
 * @property int $month
 * @property string $report_type
 * @property array $salon_ids
 * @property array|null $salon_data
 * @property int $total_salons
 */
class BeautyMonthlyReport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'report_type' => 'string',
        'salon_ids' => 'array',
        'salon_data' => 'array',
        'total_salons' => 'integer',
    ];
    
    /**
     * Scope a query to filter by report type
     * محدود کردن کوئری بر اساس نوع گزارش
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }
    
    /**
     * Scope a query to filter by period
     * محدود کردن کوئری بر اساس دوره
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $year
     * @param int $month
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPeriod($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }
}

