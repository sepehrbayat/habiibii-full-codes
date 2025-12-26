<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Modules\BeautyBooking\Entities\BeautyServiceCategory;
use Modules\BeautyBooking\Http\Requests\BeautyCategoryStoreRequest;
use Modules\BeautyBooking\Http\Requests\BeautyCategoryUpdateRequest;
use App\CentralLogics\Helpers;
use Maatwebsite\Excel\Facades\Excel;
use Modules\BeautyBooking\Exports\BeautyCategoryExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Category Controller (Admin)
 * کنترلر دسته‌بندی خدمات (ادمین)
 */
class BeautyCategoryController extends Controller
{
    public function __construct(
        private BeautyServiceCategory $category
    ) {}

    /**
     * List all categories
     * لیست تمام دسته‌بندی‌ها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        $categories = $this->category->with('parent')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->paginate(config('default_pagination'));
        
        $language = getWebConfig('language');
        $defaultLang = str_replace('_', '-', app()->getLocale());

        return view('beautybooking::admin.category.index', compact('categories', 'language', 'defaultLang'));
    }

    /**
     * Store new category
     * ذخیره دسته‌بندی جدید
     *
     * @param BeautyCategoryStoreRequest $request
     * @return RedirectResponse
     */
    public function store(BeautyCategoryStoreRequest $request): RedirectResponse
    {

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = Helpers::upload('beauty/categories/', 'png', $request->file('image'));
            }

            $category = $this->category->create([
                'name' => $request->name,
                'parent_id' => $request->parent_id ?: null,
                'image' => $imagePath,
                'status' => $request->status ?? 1,
                'sort_order' => $request->sort_order ?? 0,
            ]);

            Helpers::add_or_update_translations(
                request: $request,
                key_data: 'name',
                name_field: 'name',
                model_name: BeautyServiceCategory::class,
                data_id: $category->id,
                data_value: $category->name,
                model_class: true
            );

            DB::commit();
            
            // Invalidate category cache
            // باطل کردن cache دسته‌بندی
            \Illuminate\Support\Facades\Cache::forget('beauty_categories_list');
            
            Toastr::success(translate('messages.category_added_successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            Toastr::error(translate('messages.failed_to_add_category'));
        }

        return back();
    }

    /**
     * Edit category
     * ویرایش دسته‌بندی
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(int $id)
    {
        $category = $this->category->withoutGlobalScope('translate')->with('translations')->findOrFail($id);
        $language = getWebConfig('language') ?? [];
        $defaultLang = str_replace('_', '-', app()->getLocale());

        return view('beautybooking::admin.category.edit', compact('category', 'language', 'defaultLang'));
    }

    /**
     * Update category
     * به‌روزرسانی دسته‌بندی
     *
     * @param BeautyCategoryUpdateRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(BeautyCategoryUpdateRequest $request, int $id): RedirectResponse
    {
        $category = $this->category->findOrFail($id);

        try {
            DB::beginTransaction();

            $data = [
                'name' => $request->name,
                'parent_id' => $request->parent_id ?: null,
                'status' => $request->status ?? $category->status,
                'sort_order' => $request->sort_order ?? $category->sort_order,
            ];

            if ($request->hasFile('image')) {
                $data['image'] = Helpers::upload('beauty/categories/', 'png', $request->file('image'));
                Helpers::delete($category->image);
            }

            $category->update($data);

            Helpers::add_or_update_translations(
                request: $request,
                key_data: 'name',
                name_field: 'name',
                model_name: BeautyServiceCategory::class,
                data_id: $category->id,
                data_value: $category->name,
                model_class: true
            );

            DB::commit();
            
            // Invalidate category cache
            // باطل کردن cache دسته‌بندی
            \Illuminate\Support\Facades\Cache::forget('beauty_categories_list');
            
            Toastr::success(translate('messages.category_updated_successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            Toastr::error(translate('messages.failed_to_update_category'));
        }

        return back();
    }

    /**
     * Delete category
     * حذف دسته‌بندی
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $category = $this->category->findOrFail($id);

        // Check if category has services
        if ($category->services()->count() > 0) {
            Toastr::error(translate('messages.cannot_delete_category_with_services'));
            return back();
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            Toastr::error(translate('messages.cannot_delete_category_with_children'));
            return back();
        }

        $category->delete();
        
        // Invalidate category cache
        // باطل کردن cache دسته‌بندی
        \Illuminate\Support\Facades\Cache::forget('beauty_categories_list');
        
        Toastr::success(translate('messages.category_deleted_successfully'));

        return back();
    }

    /**
     * Toggle category status
     * تغییر وضعیت دسته‌بندی
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function status(int $id): RedirectResponse
    {
        $category = $this->category->findOrFail($id);
        $category->update(['status' => !$category->status]);
        
        Toastr::success(translate('messages.status_updated_successfully'));
        return back();
    }

    /**
     * Export categories
     * خروجی گرفتن از دسته‌بندی‌ها
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $categories = $this->category->with('parent')
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhere('name', 'LIKE', '%' . $key . '%');
                }
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Categories',
            'data' => $categories,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautyCategoryExport($data), 'Categories.csv');
        }
        return Excel::download(new BeautyCategoryExport($data), 'Categories.xlsx');
    }
}

