<!-- Quick Action Buttons -->
<!-- دکمه‌های اقدام سریع -->
<div class="row g-3 mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ translate('Quick Actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2 col-sm-4 col-6">
                        <a href="{{ route('beauty-booking.booking.create', 0) }}" class="card text-center text-decoration-none h-100">
                            <div class="card-body">
                                <i class="tio-calendar-note fs-1 text-primary"></i>
                                <p class="mb-0 mt-2">{{ translate('Book Appointment') }}</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <a href="{{ route('beauty-booking.search') }}" class="card text-center text-decoration-none h-100">
                            <div class="card-body">
                                <i class="tio-search fs-1 text-info"></i>
                                <p class="mb-0 mt-2">{{ translate('Browse Salons') }}</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <a href="{{ route('beauty-booking.gift-cards') }}" class="card text-center text-decoration-none h-100">
                            <div class="card-body">
                                <i class="tio-gift fs-1 text-danger"></i>
                                <p class="mb-0 mt-2">{{ translate('Redeem Gift Card') }}</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <a href="{{ route('beauty-booking.loyalty') }}" class="card text-center text-decoration-none h-100">
                            <div class="card-body">
                                <i class="tio-star fs-1 text-warning"></i>
                                <p class="mb-0 mt-2">{{ translate('Check Loyalty Points') }}</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <a href="{{ route('beauty-booking.search') }}" class="card text-center text-decoration-none h-100">
                            <div class="card-body">
                                <i class="tio-package fs-1 text-success"></i>
                                <p class="mb-0 mt-2">{{ translate('Browse Packages') }}</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6">
                        <a href="{{ route('beauty-booking.my-bookings.index') }}" class="card text-center text-decoration-none h-100">
                            <div class="card-body">
                                <i class="tio-book fs-1 text-secondary"></i>
                                <p class="mb-0 mt-2">{{ translate('My Bookings') }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

