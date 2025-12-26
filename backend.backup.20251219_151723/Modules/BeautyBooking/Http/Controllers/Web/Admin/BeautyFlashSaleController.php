<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use App\Models\FlashSale;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Beauty Flash Sale Controller (Admin)
 * کنترلر فلش‌سیل ماژول زیبایی (ادمین)
 */
class BeautyFlashSaleController extends Controller
{
    /**
     * List flash sales for beauty module
     * لیست فلش‌سیل‌های ماژول زیبایی
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $moduleId = config('module.current_module_id');

        $flashSales = FlashSale::with('translations')
            ->when($moduleId, fn ($q) => $q->where('module_id', $moduleId))
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::admin.flash-sale.index', compact('flashSales'));
    }
}

