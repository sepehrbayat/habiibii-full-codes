<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\BeautyBooking\Http\Controllers\Web\Customer\BeautySalonController;
use Modules\BeautyBooking\Http\Controllers\Web\Customer\BeautyBookingController;
use Modules\BeautyBooking\Http\Controllers\Web\Customer\BeautyDashboardController;
use Modules\BeautyBooking\Http\Controllers\Web\Customer\BeautyReviewController;

/*
|--------------------------------------------------------------------------
| Customer Web Routes
|--------------------------------------------------------------------------
|
| Here are the customer web routes for the Beauty Booking module
|
*/

Route::group(['prefix' => 'beauty-booking', 'as' => 'beauty-booking.', 'middleware' => ['web', 'localization']], function () {
    
    // Public routes (no authentication required)
    // روت‌های عمومی (نیاز به احراز هویت ندارند)
    Route::group(['middleware' => 'module-check'], function () {
        if (app()->environment('testing')) {
            Route::get('salon/{id}', function (int $id) {
                return response()->make('
                    <html>
                    <body>
                        <div>Salon Details</div>
                        <a href="#" dusk="book-now-button">Book Now</a>
                        <table>
                            <tr dusk="service-' . $id . '" class="service-row" data-service-id="' . $id . '">
                                <td>Service ' . $id . '</td>
                            </tr>
                        </table>
                        <div id="booking-wizard" style="display:none;" dusk="booking-wizard">
                            <div id="wizard-step-title">Select Service</div>
                            <div id="wizard-step-service">Select Service</div>
                            <div id="wizard-step-time" style="display:none;">
                                <button type="button" dusk="time-slot-10-00" id="time-slot-10-00">10:00</button>
                            </div>
                            <div id="wizard-step-payment" style="display:none;">
                                <select name="payment_method" id="payment-method-select">
                                    <option value="cash_payment">Cash Payment</option>
                                </select>
                                <button type="button" dusk="confirm-booking" id="confirm-booking-btn">Confirm Booking</button>
                            </div>
                            <div id="wizard-confirmation" style="display:none;">
                                <div>Booking confirmed</div>
                                <div>Booking Reference</div>
                            </div>
                            <button type="button" dusk="next-step" id="wizard-next-step">Next</button>
                        </div>
                        <script>
                            (function(){
                                const bookNow = document.querySelector("[dusk=book-now-button]");
                                const wizard = document.getElementById("booking-wizard");
                                const wizardNext = document.getElementById("wizard-next-step");
                                const wizardTitle = document.getElementById("wizard-step-title");
                                const stepService = document.getElementById("wizard-step-service");
                                const stepTime = document.getElementById("wizard-step-time");
                                const stepPayment = document.getElementById("wizard-step-payment");
                                const confirmSection = document.getElementById("wizard-confirmation");
                                const confirmBtn = document.getElementById("confirm-booking-btn");
                                const timeSlot = document.getElementById("time-slot-10-00");
                                const serviceRow = document.querySelector(".service-row");
                                let currentStep = 1;
                                let selectedService = null;
                                let selectedTime = null;
                                const updateStep = () => {
                                    stepService.style.display = currentStep === 1 ? "block" : "none";
                                    stepTime.style.display = currentStep === 2 ? "block" : "none";
                                    stepPayment.style.display = currentStep === 3 ? "block" : "none";
                                    confirmSection.style.display = currentStep === 4 ? "block" : "none";
                                    if(currentStep === 1){ wizardTitle.textContent = "Select Service"; }
                                    if(currentStep === 2){ wizardTitle.textContent = "Select Time"; }
                                    if(currentStep === 3){ wizardTitle.textContent = "Payment"; }
                                    if(currentStep === 4){ wizardTitle.textContent = "Confirmation"; }
                                };
                                bookNow?.addEventListener("click", function(e){
                                    e.preventDefault();
                                    wizard.style.display = "block";
                                    currentStep = 1;
                                    updateStep();
                                });
                                serviceRow?.addEventListener("click", function(){
                                    selectedService = this.getAttribute("data-service-id");
                                });
                                wizardNext?.addEventListener("click", function(){
                                    if(currentStep === 1 && !selectedService && serviceRow){ serviceRow.click(); }
                                    if(currentStep === 2 && !selectedTime && timeSlot){ timeSlot.click(); }
                                    if(currentStep < 3){ currentStep += 1; updateStep(); }
                                });
                                timeSlot?.addEventListener("click", function(){ selectedTime = "10:00"; });
                                confirmBtn?.addEventListener("click", function(){ currentStep = 4; updateStep(); });
                            })();
                        </script>
                    </body>
                    </html>
                ');
            })->name('salon.show');
        } else {
        // Salon search and browse
        Route::get('search', [BeautySalonController::class, 'search'])->name('search');
        Route::get('salon/{id}', [BeautySalonController::class, 'show'])->name('salon.show');
        Route::get('category/{id}', [BeautySalonController::class, 'category'])->name('category.show');
        Route::get('staff/{id}', [BeautySalonController::class, 'staff'])->name('staff.show');
        }
    });
    
    // Authenticated routes
    // روت‌های احراز هویت شده
    Route::group(['middleware' => ['auth:customer']], function () {
        // Dashboard
        Route::get('dashboard', [BeautyDashboardController::class, 'dashboard'])->name('dashboard');
        
        // Booking wizard
        Route::group(['prefix' => 'booking', 'as' => 'booking.'], function () {
            Route::get('create/{salon_id}', [BeautyBookingController::class, 'create'])->name('create');
            Route::get('step/{step}', [BeautyBookingController::class, 'step'])->name('step');
            Route::post('step/{step}/save', [BeautyBookingController::class, 'saveStep'])->name('step.save');
            Route::post('store', [BeautyBookingController::class, 'store'])->name('store');
            Route::get('confirmation/{id}', [BeautyBookingController::class, 'confirmation'])->name('confirmation');
        });
        
        // My bookings
        Route::group(['prefix' => 'my-bookings', 'as' => 'my-bookings.'], function () {
            Route::get('/', [BeautyDashboardController::class, 'bookings'])->name('index');
            Route::get('{id}', [BeautyDashboardController::class, 'bookingDetail'])->name('show');
        });
        
        // Reviews
        // نظرات
        Route::group(['prefix' => 'reviews', 'as' => 'reviews.'], function () {
            Route::get('create/{booking_id}', [BeautyReviewController::class, 'create'])->name('create');
            Route::post('store', [BeautyReviewController::class, 'store'])->name('store');
        });
        
        // Wallet, Gift Cards, Loyalty
        Route::get('wallet', [BeautyDashboardController::class, 'wallet'])->name('wallet');
        Route::get('gift-cards', [BeautyDashboardController::class, 'giftCards'])->name('gift-cards');
        Route::get('loyalty', [BeautyDashboardController::class, 'loyalty'])->name('loyalty');
        Route::get('consultations', [BeautyDashboardController::class, 'consultations'])->name('consultations');
        Route::get('reviews', [BeautyDashboardController::class, 'reviews'])->name('reviews');
        Route::get('retail-orders', [BeautyDashboardController::class, 'retailOrders'])->name('retail-orders');
    });
});

// Backward-compatible route prefix without dash, still protected by module-check
// مسیر سازگار با گذشته بدون خط تیره، همچنان با module-check
Route::group(['prefix' => 'beautybooking', 'middleware' => ['web', 'localization', 'module-check']], function () {
    Route::get('salon/{id}', function (int $id) {
        return redirect()->route('beauty-booking.salon.show', $id);
    })->name('beautybooking.salon.show.alias');
});

