<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Customer;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Services\BeautyRankingService;

/**
 * Beauty Salon Controller (Customer Web)
 * کنترلر سالن (وب مشتری)
 */
class BeautySalonController extends Controller
{
    public function __construct(
        private BeautySalon $salon,
        private BeautyRankingService $rankingService
    ) {}

    /**
     * Search salons
     * جستجوی سالن‌ها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function search(Request $request)
    {
        $query = $this->salon->with(['store', 'badges'])
            ->active()
            ->verified();

        // Search by keyword
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('store', function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%');
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('services', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Location-based ranking
        if ($request->filled('latitude') && $request->filled('longitude')) {
            // Build filters array
            $filters = [];
            if ($request->filled('category_id')) {
                $filters['category_id'] = $request->category_id;
            }
            
            // Use getRankedSalons method which handles location-based ranking
            $salons = $this->rankingService->getRankedSalons(
                $request->search,
                (float) $request->latitude,
                (float) $request->longitude,
                $filters
            );
        } else {
            $salons = $query->latest()->get();
        }

        $categories = BeautyServiceCategory::where('status', 1)->get();

        return view('beautybooking::customer.search', compact('salons', 'categories'));
    }

    /**
     * Show salon details
     * نمایش جزئیات سالن
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show(int $id)
    {
        $salon = $this->salon->with([
            'store',
            'services.category',
            'staff',
            'reviews' => function($q) {
                $q->approved()->latest()->limit(10);
            },
            'badges'
        ])->findOrFail($id);

        return view('beautybooking::customer.salon.show', compact('salon'));
    }

    /**
     * Show category page
     * نمایش صفحه دسته‌بندی
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function category(int $id)
    {
        $category = BeautyServiceCategory::with('parent')->findOrFail($id);
        
        $salons = $this->salon->with(['store', 'badges'])
            ->active()
            ->verified()
            ->whereHas('services', function($q) use ($id) {
                $q->where('category_id', $id);
            })
            ->latest()
            ->paginate(12);

        return view('beautybooking::customer.category.show', compact('category', 'salons'));
    }

    /**
     * Show staff profile
     * نمایش پروفایل کارمند
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function staff(int $id)
    {
        $staff = \Modules\BeautyBooking\Entities\BeautyStaff::with(['salon.store', 'services', 'reviews'])
            ->findOrFail($id);

        return view('beautybooking::customer.staff.show', compact('staff'));
    }
}

