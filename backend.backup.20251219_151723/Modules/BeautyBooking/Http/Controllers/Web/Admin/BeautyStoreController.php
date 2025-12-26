<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use App\Models\Module;
use App\Models\Store;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Beauty Store Controller (Admin)
 * کنترلر فروشگاه ماژول زیبایی (ادمین)
 */
class BeautyStoreController extends Controller
{
    /**
     * List beauty stores
     * نمایش لیست فروشگاه‌های مربوط به ماژول زیبایی
     *
     * @param Request $request
     * @return Renderable
     */
    public function list(Request $request): Renderable
    {
        $beautyModuleId = Module::where('module_type', 'beauty')->value('id');

        $stores = Store::with(['vendor', 'zone', 'module'])
            ->when($beautyModuleId, fn ($q) => $q->where('module_id', $beautyModuleId))
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->where(function ($inner) use ($key) {
                        $inner->orWhere('name', 'like', '%' . $key . '%')
                            ->orWhere('address', 'like', '%' . $key . '%');
                    });
                }
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('beautybooking::admin.store.index', compact('stores', 'beautyModuleId'));
    }

    /**
     * Redirect to global store creation with beauty module preselected
     * هدایت به ایجاد فروشگاه با ماژول زیبایی
     *
     * @return RedirectResponse
     */
    public function createRedirect(): RedirectResponse
    {
        $beautyModuleId = Module::where('module_type', 'beauty')->value('id');
        return redirect()->route('admin.store.add', ['module_id' => $beautyModuleId]);
    }

    /**
     * Redirect to global store edit
     * هدایت به ویرایش فروشگاه
     *
     * @param int $storeId
     * @return RedirectResponse
     */
    public function editRedirect(int $storeId): RedirectResponse
    {
        return redirect()->route('admin.store.edit', $storeId);
    }
}

