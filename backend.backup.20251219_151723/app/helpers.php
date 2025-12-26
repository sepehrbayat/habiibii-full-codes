<?php

use App\Models\Admin;
use App\Models\Order;
use App\Models\Store;
use App\Models\AdminWallet;
use App\Models\DeliveryMan;
use App\Models\WalletPayment;
use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use App\Mail\OrderVerificationMail;
use App\CentralLogics\CustomerLogic;
use Illuminate\Support\Facades\Mail;
use App\Models\SubscriptionBillingAndRefundHistory;
use Brian2694\Toastr\Facades\Toastr;
use Modules\Rental\Entities\Trips;

if (! function_exists('translate')) {
    function translate($key, $replace = [])
    {
        if(strpos($key, 'validation.') === 0 || strpos($key, 'passwords.') === 0 || strpos($key, 'pagination.') === 0 || strpos($key, 'order_texts.') === 0) {
            return trans($key, $replace);
        }

        $key = strpos($key, 'messages.') === 0?substr($key,9):$key;
        $local = app()->getLocale();
        try {
            $lang_array = include(base_path('resources/lang/' . $local . '/messages.php'));
            $processed_key = ucfirst(str_replace('_', ' ', Helpers::remove_invalid_charcaters($key)));

            if (!array_key_exists($key, $lang_array)) {
                $lang_array[$key] = $processed_key;
                $str = "<?php return " . var_export($lang_array, true) . ";";
                file_put_contents(base_path('resources/lang/' . $local . '/messages.php'), $str);
                $result = $processed_key;
            } else {
                $result = trans('messages.' . $key, $replace);
            }
        } catch (\Exception $exception) {
            info($exception->getMessage());
            $result = trans('messages.' . $key, $replace);
        }

        return $result;
    }
}

if (! function_exists('collect_cash_fail')) {
    function collect_cash_fail($data){
        return 0;
    }
}
if (! function_exists('collect_cash_success')) {
    function collect_cash_success($data){

        try {
            $account_transaction = new AccountTransaction();
            if($data->attribute === 'store_collect_cash_payments'){
                $store = Store::where('vendor_id', $data->attribute_id)->first();
                $store->status = 1;
                $store->save();
                $user_data = $store?->vendor;
                $current_balance = $user_data?->wallet?->collected_cash ?? 0;
                $account_transaction->from_type = 'store';
                $account_transaction->from_id = $store?->vendor?->id;
                $account_transaction->created_by = 'store';
            }
            elseif($data->attribute === 'deliveryman_collect_cash_payments'){
                $user_data = DeliveryMan::findOrFail($data->attribute_id);
                $user_data->status = 1;
                $user_data->save();
                $current_balance = $user_data?->wallet?->collected_cash ?? 0;
                $account_transaction->from_type = 'deliveryman';
                $account_transaction->from_id = $user_data->id;
                $account_transaction->created_by = 'deliveryman';
            }
            else{
                return 0;
            }
            $account_transaction->method = $data->payment_method;
            $account_transaction->ref = $data->attribute;
            $account_transaction->amount = $data->payment_amount;
            $account_transaction->current_balance = $current_balance;

            DB::beginTransaction();
            $account_transaction->save();
            $user_data?->wallet?->decrement('collected_cash', $account_transaction->amount);
            AdminWallet::where('admin_id', Admin::where('role_id', 1)->first()->id)->increment('digital_received',  $account_transaction->amount );

            DB::commit();


        } catch (\Exception $exception) {
            info($exception->getMessage());
            DB::rollBack();

        }


        try {
            if($data->attribute == 'deliveryman_collect_cash_payments' && config('mail.status') &&  Helpers::getNotificationStatusData('deliveryman','deliveryman_collect_cash','mail_status') && Helpers::get_mail_status('cash_collect_mail_status_dm') == 1 ){
                Mail::to($user_data['email'])->send(new \App\Mail\CollectCashMail($account_transaction,$user_data['f_name']));
            }
        } catch (\Exception $exception) {
            info($exception->getMessage());
        }
        return true;
    }
}



if (! function_exists('order_place')) {
    function order_place($data) {
        $order = Order::find($data->attribute_id);
        $order->order_status='confirmed';
        if($order->payment_method != 'partial_payment'){
            $order->payment_method=$data->payment_method;
        }
        // $order->transaction_reference=$data->transaction_ref;
        $order->payment_status='paid';
        $order->confirmed=now();
        $order->save();



        if( $order?->store?->is_valid_subscription == 1 && $order?->store?->store_sub?->max_order != "unlimited" && $order?->store?->store_sub?->max_order > 0){
            $order?->store?->store_sub?->decrement('max_order' , 1);
        }


        OrderLogic::update_unpaid_order_payment(order_id:$order->id, payment_method:$data->payment_method);
        try {
            Helpers::send_order_notification($order);
            $address = json_decode($order->delivery_address, true);


            if(Helpers::getNotificationStatusData('customer','customer_delivery_verification','mail_status')  && Helpers::get_mail_status('order_verification_mail_status_user') == 1 && config('mail.status')){

                if ( config('order_delivery_verification') == 1  && $order->is_guest == 0) {
                    Mail::to($order->customer->email)->send(new OrderVerificationMail($order->otp,$order->customer->f_name));
                }

                if ($order->is_guest == 1   && isset($address['contact_person_email'])) {
                    Mail::to($address['contact_person_email'])->send(new OrderVerificationMail($order->otp,$order?->customer?->f_name));
                }
            }
        } catch (\Exception $e) {
            info($e);
        }

    }

}

if (! function_exists('trip_payment_success')) {
    function trip_payment_success($data) {
        $trip = Trips::find($data->attribute_id);
        if($trip->payment_method != 'partial_payment'){
            $trip->payment_method=$data->payment_method;
        }
        $trip->transaction_reference=$data->transaction_ref;
        $trip->payment_status='paid';
        $trip->save();

        if( $trip?->provider?->is_valid_subscription == 1 && $trip?->provider?->store_sub?->max_order != "unlimited" && $trip?->provider?->store_sub?->max_order > 0){
            $trip?->provider?->store_sub?->decrement('max_order' , 1);
        }

        if ($trip->trip_status == 'completed' && $trip->payment_status == 'paid' && !$trip->trip_transaction) {
            Helpers::createTransactionForTrip($trip, 'admin');
        }
        Helpers::sendTripPaymentNotificationCustomerMain($trip);
        OrderLogic::update_unpaid_trip_payment(trip_id:$trip->id, payment_method:$data->payment_method);
    }

}



if (! function_exists('trip_payment_fail')) {
    function trip_payment_fail($data) {
        $trip = Trips::find($data->attribute_id);
        $trip->trip_status='payment_failed';
        if($trip->payment_method != 'partial_payment'){
            $trip->payment_method=$data->payment_method;
        }
        $trip->payment_failed=now();
        $trip->save();
        return true;
    }
}



if (! function_exists('order_failed')) {
    function order_failed($data) {
        $order = Order::find($data->attribute_id);
        $order->order_status='failed';
        if($order->payment_method != 'partial_payment'){
            $order->payment_method=$data->payment_method;
        }
        $order->failed=now();
        $order->save();
    }
}

if (! function_exists('wallet_success')) {
    function wallet_success($data) {
        $order = WalletPayment::find($data->attribute_id);
        $order->payment_method=$data->payment_method;
        // $order->transaction_reference=$data->transaction_ref;
        $order->payment_status='success';
        $order->save();
        $wallet_transaction = CustomerLogic::create_wallet_transaction($data->payer_id, $data->payment_amount, 'add_fund',$data->payment_method);
        if($wallet_transaction)
        {
            try{
                Helpers::add_fund_push_notification($data->payer_id);
                if(config('mail.status') && Helpers::get_mail_status('add_fund_mail_status_user') == '1' &&  Helpers::getNotificationStatusData('customer','customer_add_fund_to_wallet','mail_status')) {
                    Mail::to($wallet_transaction->user->email)->send(new \App\Mail\AddFundToWallet($wallet_transaction));
                }
            }catch(\Exception $ex)
            {
                info($ex->getMessage());
            }
        }
    }
}

if (! function_exists('wallet_success')) {
    function wallet_failed($data) {
        $order = WalletPayment::find($data->attribute_id);
        $order->payment_status='failed';
        $order->payment_method=$data->payment_method;
        $order->save();
    }
}

if (!function_exists('addon_published_status')) {
    function addon_published_status($module_name)
    {
        $is_published = 0;
        try {
            // Use base_path() to ensure correct path regardless of current working directory
            // استفاده از base_path() برای اطمینان از مسیر صحیح بدون توجه به دایرکتوری فعلی
            $info_path = base_path("Modules/{$module_name}/Addon/info.php");
            if (file_exists($info_path)) {
                $full_data = include($info_path);
                $is_published = $full_data['is_published'] == 1 ? 1 : 0;
            }
            return $is_published;
        } catch (\Exception $exception) {
            return 0;
        }
    }
}



if (!function_exists('config_settings')) {
    function config_settings($key, $settings_type)
    {
        try {
            $config = DB::table('addon_settings')->where('key_name', $key)
                ->where('settings_type', $settings_type)->first();
        } catch (Exception $exception) {
            return null;
        }
        return (isset($config)) ? $config : null;
    }
}

if (! function_exists('sub_success')) {
    function sub_success($data){
        $type='renew';
        if($data->attribute == 'store_subscription_payment'){
            $type='new_plan';
        } elseif($data->attribute == 'store_subscription_new_join'){
            $type='new_join';
        }

        $pending_bill= SubscriptionBillingAndRefundHistory::where(['store_id'=>$data->payer_id,
        'transaction_type'=>'pending_bill', 'is_success' =>0])?->sum('amount')?? 0;
        Helpers::subscription_plan_chosen(store_id:$data->payer_id,package_id:$data->attribute_id,payment_method:$data->payment_method,discount:0,pending_bill:$pending_bill,reference:$data->attribute,type: $type);
        if($type !== 'new_join'){
            Toastr::success(  $type == 'renew' ?  translate('Subscription_Package_Renewed_Successfully.'): translate('Subscription_Package_Shifted_Successfully.')  );
        }

        return true;
    }
}

if (! function_exists('sub_fail')) {
    function sub_fail($data){
        return true;
    }
}

// Beauty Booking Payment Hooks
// Hook های پرداخت رزرو زیبایی
if (! function_exists('beauty_booking_payment_success')) {
    /**
     * Handle beauty booking payment success
     * مدیریت موفقیت پرداخت رزرو زیبایی
     *
     * @param object $data Payment data object
     * @return void
     */
    function beauty_booking_payment_success($data) {
        // Check if BeautyBooking module is published before accessing its classes
        // بررسی اینکه آیا ماژول BeautyBooking منتشر شده است قبل از دسترسی به کلاس‌های آن
        if (!addon_published_status('BeautyBooking')) {
            return;
        }
        
        try {
            // Eager load relationships to avoid N+1 queries
            // بارگذاری eager روابط برای جلوگیری از کوئری‌های N+1
            $booking = \Modules\BeautyBooking\Entities\BeautyBooking::with(['salon.store.store_sub', 'user'])
                ->find($data->attribute_id);
            if (!$booking) {
                \Log::error('Beauty booking not found for payment success', [
                    'attribute_id' => $data->attribute_id,
                ]);
                return;
            }

            // Update payment method and status
            // به‌روزرسانی روش پرداخت و وضعیت
            if($booking->payment_method != 'partial_payment'){
                $booking->payment_method = $data->payment_method ?? $booking->payment_method;
            }
            
            // Save transaction reference if available
            // ذخیره شماره تراکنش در صورت موجود بودن
            if(isset($data->transaction_ref) && $data->transaction_ref) {
                // Store in notes if no dedicated field exists
                // ذخیره در notes اگر فیلد اختصاصی وجود ندارد
                $notes = $booking->notes ? json_decode($booking->notes, true) : [];
                if (!is_array($notes)) {
                    $notes = [];
                }
                $notes['transaction_reference'] = $data->transaction_ref;
                $booking->notes = json_encode($notes);
            }

            $booking->payment_status = 'paid';
            $booking->save();

            // Handle subscription max order if applicable
            // مدیریت max order اشتراک در صورت اعمال
            // Use null-safe operator to safely access salon relationship
            // استفاده از null-safe operator برای دسترسی ایمن به رابطه salon
            $salon = $booking->salon;
            if ($salon?->store) {
                $store = $salon->store;
                if ($store?->is_valid_subscription == 1 && $store?->store_sub) {
                    $maxOrder = (string) $store->store_sub->max_order;
                    // Check if max_order is not "unlimited" and is a valid positive number
                    // بررسی اینکه max_order "unlimited" نیست و یک عدد مثبت معتبر است
                    if ($maxOrder !== "unlimited" && is_numeric($maxOrder) && (int) $maxOrder > 0) {
                        $store->store_sub->decrement('max_order', 1);
                    }
                }
            }

            // Record commission and revenue if booking is confirmed
            // ثبت کمیسیون و درآمد در صورت تأیید رزرو
            // Note: Commission should be recorded when booking is confirmed, not just when payment is received
            // Commission recording is handled in BeautyBookingService->updateBookingStatus() when status changes to 'confirmed'
            // توجه: کمیسیون باید زمانی ثبت شود که رزرو تأیید شده باشد، نه فقط زمانی که پرداخت دریافت شد
            // ثبت کمیسیون در BeautyBookingService->updateBookingStatus() انجام می‌شود وقتی وضعیت به 'confirmed' تغییر می‌کند
            // We only record here if booking is already confirmed when payment succeeds
            // فقط در اینجا ثبت می‌کنیم اگر رزرو قبلاً تأیید شده باشد وقتی پرداخت موفق می‌شود
            if ($booking->status === 'confirmed' && $booking->payment_status === 'paid') {
                try {
                    $revenueService = app(\Modules\BeautyBooking\Services\BeautyRevenueService::class);
                    // Check if commission was already recorded to avoid duplicates
                    // بررسی اینکه آیا کمیسیون قبلاً ثبت شده است تا از تکرار جلوگیری شود
                    $existingTransaction = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
                        ->where('transaction_type', 'commission')
                        ->exists();
                     
                    if (!$existingTransaction) {
                        $revenueService->recordCommission($booking);
                        $revenueService->recordServiceFee($booking);
                        
                        // Record package sale revenue if booking uses a package
                        // ثبت درآمد فروش پکیج در صورت استفاده از پکیج
                        if ($booking->package_id) {
                            $package = \Modules\BeautyBooking\Entities\BeautyPackage::find($booking->package_id);
                            if ($package) {
                                $existingPackageSale = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
                                    ->where('transaction_type', 'package_sale')
                                    ->exists();
                                
                                if (!$existingPackageSale) {
                                    $revenueService->recordPackageSale($package, $booking);
                                }
                            }
                        }
                        
                        // Record consultation fee if this is a consultation booking
                        // ثبت هزینه مشاوره در صورت اینکه این رزرو مشاوره باشد
                        $service = \Modules\BeautyBooking\Entities\BeautyService::find($booking->service_id);
                        if ($service && $service->isConsultation()) {
                            $existingConsultationFee = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
                                ->where('transaction_type', 'consultation_fee')
                                ->exists();
                            
                            if (!$existingConsultationFee) {
                                $revenueService->recordConsultationFee($booking);
                            }
                        }
                        
                        // Record cross-selling revenue if additional services were purchased
                        // ثبت درآمد فروش متقابل در صورت خرید خدمات اضافی
                        if ($booking->additional_services && is_array($booking->additional_services) && count($booking->additional_services) > 0) {
                            $existingCrossSelling = \Modules\BeautyBooking\Entities\BeautyTransaction::where('booking_id', $booking->id)
                                ->where('transaction_type', 'cross_selling')
                                ->exists();
                            
                            if (!$existingCrossSelling) {
                                $revenueService->recordCrossSellingRevenue($booking, $booking->additional_services);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to record commission/revenue for booking', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Send payment success notifications
            // ارسال نوتیفیکیشن موفقیت پرداخت
            try {
                // Call the static method from the trait
                // فراخوانی متد static از trait
                \Modules\BeautyBooking\Services\BeautyBookingService::sendBookingNotificationToAll($booking, 'payment_success');
            } catch (\Exception $e) {
                \Log::error('Failed to send payment success notification', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Beauty booking payment success hook failed', [
                'attribute_id' => $data->attribute_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

if (! function_exists('beauty_booking_payment_fail')) {
    /**
     * Handle beauty booking payment failure
     * مدیریت شکست پرداخت رزرو زیبایی
     *
     * @param object $data Payment data object
     * @return void
     */
    function beauty_booking_payment_fail($data) {
        // Check if BeautyBooking module is published before accessing its classes
        // بررسی اینکه آیا ماژول BeautyBooking منتشر شده است قبل از دسترسی به کلاس‌های آن
        if (!addon_published_status('BeautyBooking')) {
            return;
        }
        
        try {
            $booking = \Modules\BeautyBooking\Entities\BeautyBooking::find($data->attribute_id);
            if (!$booking) {
                \Log::error('Beauty booking not found for payment failure', [
                    'attribute_id' => $data->attribute_id,
                ]);
                return;
            }

            // Update payment status to failed
            // به‌روزرسانی وضعیت پرداخت به شکست خورده
            if($booking->payment_method != 'partial_payment'){
                $booking->payment_method = $data->payment_method ?? $booking->payment_method;
            }

            // Set payment_status to 'unpaid' since payment failed
            // تنظیم payment_status به 'unpaid' چون پرداخت شکست خورده است
            // Note: payment_status enum values are 'paid', 'unpaid', 'partially_paid' - no 'failed' value
            // توجه: مقادیر enum payment_status عبارتند از 'paid', 'unpaid', 'partially_paid' - مقدار 'failed' وجود ندارد
            // Explicitly set to unpaid to indicate payment failure
            // به صراحت به 'unpaid' تنظیم می‌کنیم تا شکست پرداخت مشخص شود
            $booking->payment_status = 'unpaid';

            // Update booking status to indicate payment failure
            // به‌روزرسانی وضعیت رزرو برای نشان دادن شکست پرداخت
            // We'll use notes to store payment_failed timestamp since there's no dedicated field
            // از notes برای ذخیره زمان شکست پرداخت استفاده می‌کنیم چون فیلد اختصاصی وجود ندارد
            $notes = $booking->notes ? json_decode($booking->notes, true) : [];
            if (!is_array($notes)) {
                $notes = [];
            }
            $notes['payment_failed_at'] = now()->toDateTimeString();
            $booking->notes = json_encode($notes);

            // Keep status as pending or update to a failed status if needed
            // حفظ وضعیت به عنوان pending یا به‌روزرسانی به وضعیت failed در صورت نیاز
            // Note: We don't change status to 'cancelled' automatically to allow retry
            // توجه: وضعیت را به 'cancelled' تغییر نمی‌دهیم تا امکان تلاش مجدد باشد
            $booking->save();

            // Send payment failure notifications
            // ارسال نوتیفیکیشن شکست پرداخت
            try {
                // Call the static method from the trait
                // فراخوانی متد static از trait
                \Modules\BeautyBooking\Services\BeautyBookingService::sendBookingNotificationToAll($booking, 'payment_failed');
            } catch (\Exception $e) {
                \Log::error('Failed to send payment failure notification', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Beauty booking payment fail hook failed', [
                'attribute_id' => $data->attribute_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

// Beauty Subscription Payment Hooks
// Hook های پرداخت اشتراک زیبایی
if (! function_exists('beauty_subscription_payment_success')) {
    /**
     * Handle beauty subscription payment success
     * مدیریت موفقیت پرداخت اشتراک زیبایی
     *
     * @param object $data Payment data object
     * @return void
     */
    function beauty_subscription_payment_success($data) {
        // Check if BeautyBooking module is published before accessing its classes
        // بررسی اینکه آیا ماژول BeautyBooking منتشر شده است قبل از دسترسی به کلاس‌های آن
        if (!addon_published_status('BeautyBooking')) {
            return;
        }
        
        try {
            // Eager load relationships to avoid N+1 queries
            // بارگذاری eager روابط برای جلوگیری از کوئری‌های N+1
            $subscription = \Modules\BeautyBooking\Entities\BeautySubscription::with(['salon.store'])
                ->find($data->attribute_id);
            if (!$subscription) {
                \Log::error('Beauty subscription not found for payment success', [
                    'attribute_id' => $data->attribute_id,
                ]);
                return;
            }

            // Update subscription status to active and payment details
            // به‌روزرسانی وضعیت اشتراک به active و جزئیات پرداخت
            $subscription->status = 'active';
            
            // Handle amount_paid: accumulate for partial payments, replace for full payments
            // مدیریت amount_paid: جمع برای پرداخت‌های جزئی، جایگزینی برای پرداخت‌های کامل
            if ($subscription->payment_method === 'partial_payment') {
                // Accumulate payment amount for partial payments
                // جمع کردن مبلغ پرداخت برای پرداخت‌های جزئی
                $subscription->amount_paid = ($subscription->amount_paid ?? 0) + ($data->payment_amount ?? 0);
            } else {
                // Replace payment amount for full payments
                // جایگزینی مبلغ پرداخت برای پرداخت‌های کامل
                $subscription->amount_paid = $data->payment_amount ?? $subscription->amount_paid;
            }
            
            if($subscription->payment_method != 'partial_payment'){
                $subscription->payment_method = $data->payment_method ?? $subscription->payment_method;
            }
            $subscription->save();

            // Update salon featured status if subscription type is 'featured'
            // به‌روزرسانی وضعیت Featured سالن در صورت اینکه نوع اشتراک 'featured' باشد
            if ($subscription->subscription_type === 'featured' && $subscription->salon) {
                $subscription->salon->update(['is_featured' => true]);
            }

            // Record revenue
            // ثبت درآمد
            try {
                $revenueService = app(\Modules\BeautyBooking\Services\BeautyRevenueService::class);
                // Check if revenue was already recorded for THIS specific subscription to avoid duplicates
                // بررسی اینکه آیا درآمد این اشتراک خاص قبلاً ثبت شده است تا از تکرار جلوگیری شود
                // Use reference_number to store subscription ID since subscription_id field doesn't exist
                // استفاده از reference_number برای ذخیره subscription ID چون فیلد subscription_id وجود ندارد
                $existingTransaction = \Modules\BeautyBooking\Entities\BeautyTransaction::where('salon_id', $subscription->salon_id)
                    ->where('transaction_type', 'subscription')
                    ->where('booking_id', null)
                    ->where('reference_number', 'subscription_' . $subscription->id)
                    ->exists();
                 
                if (!$existingTransaction) {
                    $revenueService->recordSubscription($subscription);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to record subscription revenue', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Update badge if needed
            // به‌روزرسانی نشان در صورت نیاز
            if ($subscription->subscription_type === 'featured') {
                try {
                    $badgeService = app(\Modules\BeautyBooking\Services\BeautyBadgeService::class);
                    $badgeService->assignBadgeIfNotExists($subscription->salon_id, 'featured', $subscription->end_date);
                } catch (\Exception $e) {
                    \Log::error('Failed to assign featured badge', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Beauty subscription payment success hook failed', [
                'attribute_id' => $data->attribute_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

if (! function_exists('beauty_subscription_payment_fail')) {
    /**
     * Handle beauty subscription payment failure
     * مدیریت شکست پرداخت اشتراک زیبایی
     *
     * @param object $data Payment data object
     * @return void
     */
    function beauty_subscription_payment_fail($data) {
        // Check if BeautyBooking module is published before accessing its classes
        // بررسی اینکه آیا ماژول BeautyBooking منتشر شده است قبل از دسترسی به کلاس‌های آن
        if (!addon_published_status('BeautyBooking')) {
            return;
        }
        
        try {
            $subscription = \Modules\BeautyBooking\Entities\BeautySubscription::find($data->attribute_id);
            if (!$subscription) {
                \Log::error('Beauty subscription not found for payment failure', [
                    'attribute_id' => $data->attribute_id,
                ]);
                return;
            }

            // Update subscription status to cancelled
            // به‌روزرسانی وضعیت اشتراک به cancelled
            $subscription->status = 'cancelled';
            if($subscription->payment_method != 'partial_payment'){
                $subscription->payment_method = $data->payment_method ?? $subscription->payment_method;
            }
            $subscription->save();

            // Note: Subscription was created with pending status, so we can safely cancel it
            // توجه: اشتراک با وضعیت pending ایجاد شده است، پس می‌توانیم آن را به راحتی لغو کنیم

        } catch (\Exception $e) {
            \Log::error('Beauty subscription payment fail hook failed', [
                'attribute_id' => $data->attribute_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

// Hook های پرداخت سفارش خرده‌فروشی زیبایی
if (! function_exists('beauty_retail_order_payment_success')) {
    /**
     * Handle beauty retail order payment success
     * مدیریت موفقیت پرداخت سفارش خرده‌فروشی زیبایی
     *
     * @param object $data Payment data object
     * @return void
     */
    function beauty_retail_order_payment_success($data) {
        // Check if BeautyBooking module is published before accessing its classes
        // بررسی اینکه آیا ماژول BeautyBooking منتشر شده است قبل از دسترسی به کلاس‌های آن
        if (!addon_published_status('BeautyBooking')) {
            return;
        }
        
        try {
            $order = \Modules\BeautyBooking\Entities\BeautyRetailOrder::with(['salon', 'user'])
                ->find($data->attribute_id);
            if (!$order) {
                \Log::error('Beauty retail order not found for payment success', [
                    'attribute_id' => $data->attribute_id,
                ]);
                return;
            }

            // Update payment method and status
            // به‌روزرسانی روش پرداخت و وضعیت
            if($order->payment_method != 'partial_payment'){
                $order->payment_method = $data->payment_method ?? $order->payment_method;
            }
            
            // Save transaction reference if available
            // ذخیره شماره تراکنش در صورت موجود بودن
            if(isset($data->transaction_ref) && $data->transaction_ref) {
                // Store in notes if no dedicated field exists
                // ذخیره در notes اگر فیلد اختصاصی وجود ندارد
                $notes = $order->notes ? json_decode($order->notes, true) : [];
                if (!is_array($notes)) {
                    $notes = [];
                }
                $notes['transaction_reference'] = $data->transaction_ref;
                $order->notes = json_encode($notes);
            }

            $order->payment_status = 'paid';
            $order->save();

            // Record revenue
            // ثبت درآمد
            $retailService = app(\Modules\BeautyBooking\Services\BeautyRetailService::class);
            $retailService->recordRevenue($order);

            // Send notification
            // ارسال اعلان
            try {
                if ($order->user) {
                    $fcmToken = $order->user->cm_firebase_token;
                    if ($fcmToken) {
                        $notificationData = [
                            'title' => translate('retail_order_payment_success'),
                            'description' => translate('your_retail_order_has_been_paid_successfully'),
                            'order_id' => $order->id,
                            'type' => 'retail_order_payment_success',
                        ];
                        \App\CentralLogics\Helpers::send_push_notif_to_device($fcmToken, $notificationData);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send retail order payment success notification', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Beauty retail order payment success hook failed', [
                'attribute_id' => $data->attribute_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

if (! function_exists('beauty_retail_order_payment_fail')) {
    /**
     * Handle beauty retail order payment failure
     * مدیریت شکست پرداخت سفارش خرده‌فروشی زیبایی
     *
     * @param object $data Payment data object
     * @return void
     */
    function beauty_retail_order_payment_fail($data) {
        // Check if BeautyBooking module is published before accessing its classes
        // بررسی اینکه آیا ماژول BeautyBooking منتشر شده است قبل از دسترسی به کلاس‌های آن
        if (!addon_published_status('BeautyBooking')) {
            return;
        }
        
        try {
            $order = \Modules\BeautyBooking\Entities\BeautyRetailOrder::find($data->attribute_id);
            if (!$order) {
                \Log::error('Beauty retail order not found for payment failure', [
                    'attribute_id' => $data->attribute_id,
                ]);
                return;
            }

            // Update payment status to unpaid (since payment failed)
            // به‌روزرسانی وضعیت پرداخت به unpaid (چون پرداخت شکست خورده است)
            // Note: payment_status enum values are 'paid', 'unpaid', 'partially_paid' - no 'failed' value
            // توجه: مقادیر enum payment_status عبارتند از 'paid', 'unpaid', 'partially_paid' - مقدار 'failed' وجود ندارد
            $order->payment_status = 'unpaid';
            if($order->payment_method != 'partial_payment'){
                $order->payment_method = $data->payment_method ?? $order->payment_method;
            }
            $order->save();

            // Send notification
            // ارسال اعلان
            try {
                if ($order->user) {
                    $fcmToken = $order->user->cm_firebase_token;
                    if ($fcmToken) {
                        $notificationData = [
                            'title' => translate('retail_order_payment_failed'),
                            'description' => translate('your_retail_order_payment_has_failed'),
                            'order_id' => $order->id,
                            'type' => 'retail_order_payment_fail',
                        ];
                        \App\CentralLogics\Helpers::send_push_notif_to_device($fcmToken, $notificationData);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send retail order payment fail notification', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Beauty retail order payment fail hook failed', [
                'attribute_id' => $data->attribute_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

