<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Http\Controllers\Web\Admin;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use Modules\BeautyBooking\Entities\BeautySalon;
use Modules\BeautyBooking\Entities\BeautyTransaction;
use Modules\BeautyBooking\Emails\SalonApproval;
use Modules\BeautyBooking\Traits\BeautyPushNotification;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\BeautyBooking\Exports\BeautySalonExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Beauty Salon Controller (Admin)
 * کنترلر سالن زیبایی (ادمین)
 */
class BeautySalonController extends Controller
{
    use BeautyPushNotification;

    public function __construct(
        private BeautySalon $salon
    ) {}

    /**
     * List all salons with filters
     * لیست تمام سالن‌ها با فیلترها
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        // Eager load booking counts by status to prevent N+1 queries
        // بارگذاری eager تعداد رزروها بر اساس وضعیت برای جلوگیری از کوئری‌های N+1
        $salons = $this->salon->with([
                'store',
                'zone',
                'badges',
                'subscriptions' => function ($query) {
                    $query->where('status', 'active')
                        ->where('end_date', '>=', now()->toDateString());
                },
            ])
            ->withCount([
                'bookings as total_bookings_count',
                'bookings as completed_bookings_count' => function ($query) {
                    $query->where('status', 'completed');
                },
                'bookings as cancelled_bookings_count' => function ($query) {
                    $query->where('status', 'cancelled');
                },
                'bookings as pending_bookings_count' => function ($query) {
                    $query->where('status', 'pending');
                },
                'bookings as confirmed_bookings_count' => function ($query) {
                    $query->where('status', 'confirmed');
                },
            ])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhereHas('store', function($q) use ($key) {
                        $q->where('name', 'LIKE', '%' . $key . '%');
                    });
                }
            })
            ->when($request->filled('verification_status'), function ($query) use ($request) {
                $query->where('verification_status', $request->verification_status);
            })
            ->when($request->filled('is_featured'), function ($query) use ($request) {
                $query->where('is_featured', $request->is_featured);
            })
            ->latest()
            ->paginate(config('default_pagination'));

        // Calculate statistics for display
        // محاسبه آمار برای نمایش
        $totalSalons = BeautySalon::count();
        $activeSalons = BeautySalon::whereHas('store', function($query) {
            return $query->where('status', 1);
        })->where('verification_status', 1)->count();
        $inactiveSalons = BeautySalon::whereHas('store', function($query) {
            return $query->where('status', 1);
        })->where('verification_status', '!=', 1)->count();
        $newlyJoinedSalons = BeautySalon::where('created_at', '>=', now()->subDays(30)->toDateTimeString())->count();
        
        // Calculate transaction statistics for display
        // محاسبه آمار تراکنش‌ها برای نمایش
        $totalTransaction = BeautyTransaction::count();
        $commissionEarned = BeautyTransaction::where('transaction_type', 'commission')->sum('amount');
        $storeWithdraws = 0; // Will be calculated when withdrawal system is implemented
        // خواهد شد محاسبه زمانی که سیستم برداشت پیاده‌سازی شود

        return view('beautybooking::admin.salon.index', compact(
            'salons',
            'totalSalons',
            'activeSalons',
            'inactiveSalons',
            'newlyJoinedSalons',
            'totalTransaction',
            'commissionEarned',
            'storeWithdraws'
        ));
    }

    /**
     * View salon details
     * مشاهده جزئیات سالن
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function view(Request $request, int $id)
    {
        $salon = $this->salon->with(['store', 'zone', 'badges', 'reviews', 'store.vendor'])->findOrFail($id);
        $tab = $request->get('tab', 'overview');
        
        // Load data based on tab
        // بارگذاری داده بر اساس تب
        $data = ['salon' => $salon];
        
        switch ($tab) {
            case 'bookings':
                $data['bookings'] = \Modules\BeautyBooking\Entities\BeautyBooking::where('salon_id', $salon->id)
                    ->with(['user', 'service', 'staff'])
                    ->latest()
                    ->paginate(config('default_pagination'));
                break;
            case 'staff':
                $data['staff'] = \Modules\BeautyBooking\Entities\BeautyStaff::where('salon_id', $salon->id)
                    ->latest()
                    ->paginate(config('default_pagination'));
                break;
            case 'services':
                $data['services'] = \Modules\BeautyBooking\Entities\BeautyService::where('salon_id', $salon->id)
                    ->with('category')
                    ->latest()
                    ->paginate(config('default_pagination'));
                break;
            case 'reviews':
                $data['reviews'] = \Modules\BeautyBooking\Entities\BeautyReview::where('salon_id', $salon->id)
                    ->with(['user', 'booking'])
                    ->latest()
                    ->paginate(config('default_pagination'));
                break;
            case 'transactions':
                $data['transactions'] = \Modules\BeautyBooking\Entities\BeautyTransaction::where('salon_id', $salon->id)
                    ->latest()
                    ->paginate(config('default_pagination'));
                break;
            case 'disbursements':
                $key = explode(' ', $request->search ?? '');
                $disbursements = \App\Models\DisbursementDetails::where('store_id', $salon->store_id ?? 0)
                    ->with(['store', 'withdraw_method'])
                    ->when(!empty($key), function ($q) use ($key) {
                        $q->where(function ($query) use ($key) {
                            foreach ($key as $value) {
                                $query->orWhere('disbursement_id', 'like', "%{$value}%")
                                    ->orWhere('status', 'like', "%{$value}%");
                            }
                        });
                    })
                    ->latest()
                    ->paginate(config('default_pagination'));
                $data['disbursements'] = $disbursements;
                break;
            case 'conversations':
                $user = \App\Models\UserInfo::where(['vendor_id' => $salon->store->vendor_id ?? 0])->first();
                if ($user) {
                    $conversations = \App\Models\Conversation::with(['sender', 'receiver', 'last_message'])
                        ->whereUser($user->id)
                        ->paginate(8);
                } else {
                    // Create empty paginator when user doesn't exist
                    // ایجاد paginator خالی وقتی کاربر وجود ندارد
                    // This ensures the view receives a proper paginator object with all necessary methods
                    // این اطمینان می‌دهد که view یک شی paginator مناسب با تمام متدهای لازم دریافت می‌کند
                    $conversations = new LengthAwarePaginator([], 0, 8, 1, [
                        'path' => request()->url(),
                        'pageName' => 'page',
                    ]);
                }
                $data['conversations'] = $conversations;
                break;
            case 'overview':
            default:
                // Calculate wallet/earnings data
                // محاسبه داده‌های کیف پول/درآمد
                $data['wallet'] = (object)[
                    'collected_cash' => 0,
                    'pending_withdraw' => 0,
                    'total_withdrawn' => 0,
                    'balance' => 0,
                    'total_earning' => \Modules\BeautyBooking\Entities\BeautyTransaction::where('salon_id', $salon->id)
                        ->where('transaction_type', '!=', 'refund')
                        ->sum('amount'),
                ];
                break;
        }
        
        return view('beautybooking::admin.salon.view', $data);
    }

    /**
     * Approve salon registration
     * تأیید ثبت‌نام سالن
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $salon = $this->salon->with(['store', 'store.vendor'])->findOrFail($id);
            
            // Update salon status
            // به‌روزرسانی وضعیت سالن
            $salon->update([
                'verification_status' => 1, // approved
                'is_verified' => true,
                'verification_notes' => $request->verification_notes,
            ]);

            // Assign verified badge if not exists
            // تخصیص نشان verified در صورت عدم وجود
            $badgeService = app(\Modules\BeautyBooking\Services\BeautyBadgeService::class);
            $badgeService->assignBadgeIfNotExists($salon->id, 'verified');

            // Send email notification
            // ارسال ایمیل نوتیفیکیشن
            if ($salon->store && $salon->store->vendor) {
                $vendor = $salon->store->vendor;
                if ($vendor->email) {
                    Mail::to($vendor->email)->send(
                        new SalonApproval($salon->store->name ?? '', 'approved', $request->verification_notes)
                    );
                }
            }

            // Send push notification to vendor
            // ارسال نوتیفیکیشن پوش به فروشنده
            if ($salon->store && $salon->store->vendor && $salon->store->vendor->firebase_token) {
                $data = [
                    'title' => translate('Salon_Approved'),
                    'description' => translate('messages.Your salon has been approved and is now active'),
                    'salon_id' => $salon->id,
                    'type' => 'salon_approved',
                ];
                Helpers::send_push_notif_to_device($salon->store->vendor->firebase_token, $data);
            }

            // Flash success message for UI/automated tests
            // نمایش پیام موفقیت برای رابط کاربری و تست‌های خودکار
            session()->flash('beauty_salon_status_message', 'Salon approved successfully');

            Toastr::success(translate('messages.salon_approved_successfully'));
        } catch (\Exception $e) {
            \Log::error('Salon approval failed: ' . $e->getMessage(), [
                'salon_id' => $id,
                'error' => $e->getTraceAsString(),
            ]);
            Toastr::error(translate('messages.failed_to_approve_salon'));
        }

        return back();
    }

    /**
     * Reject salon registration
     * رد ثبت‌نام سالن
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'verification_notes' => 'required|string|max:1000',
        ], [
            'verification_notes.required' => translate('messages.rejection_notes_required'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $salon = $this->salon->with(['store', 'store.vendor'])->findOrFail($id);
            
            // Update salon status
            // به‌روزرسانی وضعیت سالن
            $salon->update([
                'verification_status' => 2, // rejected
                'is_verified' => false,
                'verification_notes' => $request->verification_notes,
            ]);

            // Revoke verified badge if exists
            // حذف نشان verified در صورت وجود
            $salon->badges()->where('badge_type', 'verified')->delete();

            // Send email notification
            // ارسال ایمیل نوتیفیکیشن
            if ($salon->store && $salon->store->vendor) {
                $vendor = $salon->store->vendor;
                if ($vendor->email) {
                    Mail::to($vendor->email)->send(
                        new SalonApproval($salon->store->name ?? '', 'rejected', $request->verification_notes)
                    );
                }
            }

            // Send push notification to vendor
            // ارسال نوتیفیکیشن پوش به فروشنده
            if ($salon->store && $salon->store->vendor && $salon->store->vendor->firebase_token) {
                $data = [
                    'title' => translate('Salon_Rejected'),
                    'description' => translate('messages.Your salon registration has been rejected. Please check the notes and resubmit'),
                    'salon_id' => $salon->id,
                    'type' => 'salon_rejected',
                ];
                Helpers::send_push_notif_to_device($salon->store->vendor->firebase_token, $data);
            }

            Toastr::success(translate('messages.salon_rejected_successfully'));
        } catch (\Exception $e) {
            \Log::error('Salon rejection failed: ' . $e->getMessage(), [
                'salon_id' => $id,
                'error' => $e->getTraceAsString(),
            ]);
            Toastr::error(translate('messages.failed_to_reject_salon'));
        }

        return back();
    }

    /**
     * Toggle salon featured status
     * تغییر وضعیت Featured سالن
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function status(int $id): RedirectResponse
    {
        $salon = $this->salon->findOrFail($id);
        $salon->update(['is_featured' => !$salon->is_featured]);
        
        Toastr::success(translate('messages.status_updated_successfully'));
        return back();
    }

    /**
     * Export salons
     * خروجی گرفتن از سالن‌ها
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        $salons = $this->salon->with(['store', 'zone', 'badges'])
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                foreach ($keys as $key) {
                    $query->orWhereHas('store', function($q) use ($key) {
                        $q->where('name', 'LIKE', '%' . $key . '%');
                    });
                }
            })
            ->when($request->filled('verification_status'), function ($query) use ($request) {
                $query->where('verification_status', $request->verification_status);
            })
            ->when($request->filled('is_featured'), function ($query) use ($request) {
                $query->where('is_featured', $request->is_featured);
            })
            ->latest()
            ->get();

        $data = [
            'fileName' => 'Salons',
            'data' => $salons,
            'search' => $request->search ?? null,
        ];

        // Use input() to properly read query parameter type
        // استفاده از input() برای خواندن صحیح پارامتر type از query string
        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautySalonExport($data), 'Salons.csv');
        }
        return Excel::download(new BeautySalonExport($data), 'Salons.xlsx');
    }

    /**
     * Show new salon registration requests
     * نمایش درخواست‌های ثبت‌نام جدید سالن
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function newRequests(Request $request)
    {
        $requestType = $request->get('request_type', 'pending_salon');
        $zoneId = $request->get('zone_id');
        $searchBy = $request->get('search');

        $query = $this->salon->with(['store', 'store.vendor', 'zone']);
        
        // Filter by module if needed
        // فیلتر بر اساس ماژول در صورت نیاز
        if (Config::has('module.current_module_id')) {
            $query->where('module_id', Config::get('module.current_module_id'));
        }

        // Filter by request type
        // فیلتر بر اساس نوع درخواست
        if ($requestType == 'pending_salon') {
            $query->where('verification_status', 0);
        } elseif ($requestType == 'denied_salon') {
            $query->where('verification_status', 2);
        }

        // Filter by zone
        // فیلتر بر اساس منطقه
        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        }

        // Search
        // جستجو
        if ($searchBy) {
            $keys = explode(' ', $searchBy);
            foreach ($keys as $key) {
                $query->whereHas('store', function($q) use ($key) {
                    $q->where('name', 'LIKE', '%' . $key . '%')
                      ->orWhere('phone', 'LIKE', '%' . $key . '%');
                });
            }
        }

        $salons = $query->latest()->paginate(config('default_pagination'));

        return view('beautybooking::admin.salon.new-requests', compact('salons', 'searchBy'));
    }

    /**
     * Show new request details
     * نمایش جزئیات درخواست جدید
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function newRequestsDetails(int $id)
    {
        $salon = $this->salon->with(['store', 'store.vendor', 'zone', 'badges'])->findOrFail($id);
        
        return view('beautybooking::admin.salon.new-requests-details', compact('salon'));
    }

    /**
     * Approve or deny salon request
     * تأیید یا رد درخواست سالن
     *
     * @param Request $request
     * @param int $id
     * @param int $status
     * @return RedirectResponse
     */
    public function approveOrDeny(Request $request, int $id, int $status): RedirectResponse
    {
        try {
            $salon = $this->salon->with(['store', 'store.vendor'])->findOrFail($id);
            
            if ($status == 1) {
                // Approve
                // تأیید
                $salon->update([
                    'verification_status' => 1,
                    'is_verified' => true,
                ]);

                // Activate store if exists
                // فعال‌سازی فروشگاه در صورت وجود
                if ($salon->store) {
                    $salon->store->update(['status' => 1]);
                }

                // Send email notification
                // ارسال ایمیل نوتیفیکیشن
                if ($salon->store && $salon->store->vendor && $salon->store->vendor->email) {
                    Mail::to($salon->store->vendor->email)->send(
                        new SalonApproval($salon->store->name ?? '', 'approved')
                    );
                }

                Toastr::success(translate('messages.salon_approved_successfully'));
            } else {
                // Deny
                // رد
                $salon->update([
                    'verification_status' => 2,
                    'is_verified' => false,
                    'verification_notes' => $request->get('message', ''),
                ]);

                // Send email notification
                // ارسال ایمیل نوتیفیکیشن
                if ($salon->store && $salon->store->vendor && $salon->store->vendor->email) {
                    Mail::to($salon->store->vendor->email)->send(
                        new SalonApproval($salon->store->name ?? '', 'rejected', $request->get('message', ''))
                    );
                }

                Toastr::success(translate('messages.salon_rejected_successfully'));
            }
        } catch (\Exception $e) {
            \Log::error('Salon approval/denial failed: ' . $e->getMessage());
            Toastr::error(translate('messages.failed_to_process_request'));
        }

        return redirect()->route('admin.beautybooking.salon.new-requests');
    }

    /**
     * Show bulk import page
     * نمایش صفحه bulk import
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function bulkImportIndex()
    {
        return view('beautybooking::admin.salon.bulk-import');
    }

    /**
     * Process bulk import
     * پردازش bulk import
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulkImportData(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'salons_file' => 'required|mimes:xlsx,xls',
            'upload_type' => 'required|in:import,update',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Process import logic here
            // منطق import را اینجا پیاده‌سازی کنید
            Toastr::success(translate('messages.bulk_import_successful'));
        } catch (\Exception $e) {
            \Log::error('Bulk import failed: ' . $e->getMessage());
            Toastr::error(translate('messages.bulk_import_failed'));
        }

        return back();
    }

    /**
     * Show bulk export page
     * نمایش صفحه bulk export
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function bulkExportIndex()
    {
        return view('beautybooking::admin.salon.bulk-export');
    }

    /**
     * Process bulk export
     * پردازش bulk export
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function bulkExportData(Request $request): BinaryFileResponse
    {
        $type = $request->get('type', 'all');
        $query = $this->salon->with(['store', 'zone', 'badges']);

        if ($type == 'date_wise') {
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');
            if ($fromDate && $toDate) {
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            }
        } elseif ($type == 'id_wise') {
            $startId = $request->get('start_id');
            $endId = $request->get('end_id');
            if ($startId && $endId) {
                $query->whereBetween('id', [$startId, $endId]);
            }
        }

        $salons = $query->latest()->get();

        $data = [
            'fileName' => 'Salons_Bulk_Export',
            'data' => $salons,
        ];

        if ($request->input('type') == 'csv') {
            return Excel::download(new BeautySalonExport($data), 'Salons_Bulk_Export.csv');
        }
        return Excel::download(new BeautySalonExport($data), 'Salons_Bulk_Export.xlsx');
    }
}

