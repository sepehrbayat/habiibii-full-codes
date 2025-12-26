<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\BeautyBooking\Entities\BeautyService;
use Modules\BeautyBooking\Entities\BeautyServiceRelation;

/**
 * Beauty Service Relation Controller (Admin)
 * کنترلر روابط خدمات (ادمین)
 */
class BeautyServiceRelationController extends Controller
{
    public function index(Request $request)
    {
        $relations = BeautyServiceRelation::with(['service', 'relatedService'])
            ->when($request->filled('relation_type'), fn ($q) => $q->where('relation_type', $request->relation_type))
            ->latest()
            ->paginate(config('default_pagination'));

        $services = BeautyService::select('id', 'name', 'salon_id')->where('status', 1)->get();

        return view('beautybooking::admin.service.relations', compact('relations', 'services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|integer|exists:beauty_services,id',
            'related_service_id' => 'required|integer|different:service_id|exists:beauty_services,id',
            'relation_type' => 'required|string|in:complementary,upsell,cross_sell',
            'priority' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        BeautyServiceRelation::updateOrCreate(
            [
                'service_id' => $request->service_id,
                'related_service_id' => $request->related_service_id,
            ],
            [
                'relation_type' => $request->relation_type,
                'priority' => $request->priority ?? 0,
                'status' => 1,
            ]
        );

        return back()->with('success', translate('messages.relation_saved_successfully'));
    }

    public function destroy(int $id): RedirectResponse
    {
        BeautyServiceRelation::where('id', $id)->delete();
        return back()->with('success', translate('messages.deleted_successfully'));
    }
}

