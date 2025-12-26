<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Console\Commands;

use Illuminate\Console\Command;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyBooking;
use Modules\BeautyBooking\Entities\BeautyMonthlyReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Generate Monthly Reports Command
 * دستور تولید گزارش‌های ماهانه
 *
 * Generates monthly Top Rated Salons and Trending Clinics lists
 * تولید لیست‌های ماهانه سالن‌های دارای رتبه بالا و کلینیک‌های ترند
 */
class GenerateMonthlyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beautybooking:generate-monthly-reports {--month=} {--year=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly Top Rated Salons and Trending Clinics reports';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Starting monthly reports generation...');
        
        // Get month and year (default to previous month)
        // دریافت ماه و سال (پیش‌فرض: ماه قبل)
        $month = $this->option('month') ?: Carbon::now()->subMonth()->month;
        $year = $this->option('year') ?: Carbon::now()->subMonth()->year;
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $this->info("Generating reports for {$year}-{$month}...");
        
        // Generate Top Rated Salons list
        // تولید لیست سالن‌های دارای رتبه بالا
        $this->generateTopRatedSalons($startDate, $endDate);
        
        // Generate Trending Clinics list
        // تولید لیست کلینیک‌های ترند
        $this->generateTrendingClinics($startDate, $endDate);
        
        $this->info('Monthly reports generated successfully!');
        
        return Command::SUCCESS;
    }
    
    /**
     * Generate Top Rated Salons list
     * تولید لیست سالن‌های دارای رتبه بالا
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     */
    private function generateTopRatedSalons(Carbon $startDate, Carbon $endDate): void
    {
        $this->info('Generating Top Rated Salons list...');
        
        $topRatedSalons = BeautySalon::topRated()
            ->whereHas('bookings', function($q) use ($startDate, $endDate) {
                $q->whereBetween('booking_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->where('status', '!=', 'cancelled');
            })
            ->with(['store', 'badges'])
            ->orderByDesc('avg_rating')
            ->orderByDesc('total_bookings')
            ->limit(50)
            ->get();
        
        // Store results in database
        // ذخیره نتایج در دیتابیس
        $salonIds = $topRatedSalons->pluck('id')->toArray();
        $salonData = $topRatedSalons->map(function($salon, $index) {
            return [
                'salon_id' => $salon->id,
                'rank' => $index + 1,
                'name' => $salon->store->name ?? '',
                'avg_rating' => $salon->avg_rating,
                'total_bookings' => $salon->total_bookings,
                'total_reviews' => $salon->total_reviews,
            ];
        })->toArray();
        
        BeautyMonthlyReport::updateOrCreate(
            [
                'year' => $startDate->year,
                'month' => $startDate->month,
                'report_type' => 'top_rated_salons',
            ],
            [
                'salon_ids' => $salonIds,
                'salon_data' => $salonData,
                'total_salons' => $topRatedSalons->count(),
            ]
        );
        
        Log::info('Top Rated Salons for ' . $startDate->format('Y-m'), [
            'count' => $topRatedSalons->count(),
            'salons' => $salonIds,
        ]);
        
        $this->info("Found {$topRatedSalons->count()} top rated salons");
    }
    
    /**
     * Generate Trending Clinics list
     * تولید لیست کلینیک‌های ترند
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     */
    private function generateTrendingClinics(Carbon $startDate, Carbon $endDate): void
    {
        $this->info('Generating Trending Clinics list...');
        
        $trendingClinics = BeautySalon::where('business_type', 'clinic')
            ->whereHas('bookings', function($q) use ($startDate, $endDate) {
                $q->whereBetween('booking_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->where('status', '!=', 'cancelled');
            })
            ->withCount(['bookings' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('booking_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->where('status', '!=', 'cancelled');
            }])
            ->with(['store', 'badges'])
            ->orderByDesc('bookings_count')
            ->orderByDesc('avg_rating')
            ->limit(50)
            ->get();
        
        // Store results in database
        // ذخیره نتایج در دیتابیس
        $clinicIds = $trendingClinics->pluck('id')->toArray();
        $clinicData = $trendingClinics->map(function($clinic, $index) {
            return [
                'salon_id' => $clinic->id,
                'rank' => $index + 1,
                'name' => $clinic->store->name ?? '',
                'bookings_count' => $clinic->bookings_count,
                'avg_rating' => $clinic->avg_rating,
                'total_reviews' => $clinic->total_reviews,
            ];
        })->toArray();
        
        BeautyMonthlyReport::updateOrCreate(
            [
                'year' => $startDate->year,
                'month' => $startDate->month,
                'report_type' => 'trending_clinics',
            ],
            [
                'salon_ids' => $clinicIds,
                'salon_data' => $clinicData,
                'total_salons' => $trendingClinics->count(),
            ]
        );
        
        Log::info('Trending Clinics for ' . $startDate->format('Y-m'), [
            'count' => $trendingClinics->count(),
            'clinics' => $clinicIds,
        ]);
        
        $this->info("Found {$trendingClinics->count()} trending clinics");
    }
}

