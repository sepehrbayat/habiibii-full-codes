@if(isset($beautyWidgets['active_packages']))
<!-- Packages Widget -->
<!-- ویجت پکیج‌ها -->
<div class="card widget-card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ translate('Active Packages') }}</h5>
        <a href="{{ route('beauty-booking.search') }}" class="btn btn-sm btn-link">
            {{ translate('Browse Packages') }}
        </a>
    </div>
    <div class="card-body">
        @if($beautyWidgets['active_packages']->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($beautyWidgets['active_packages']->take(5) as $package)
                    @php
                        // Use conditional class loading to prevent ClassNotFoundException
                        // استفاده از بارگذاری شرطی کلاس برای جلوگیری از ClassNotFoundException
                        $usageClass = 'Modules\BeautyBooking\Entities\BeautyPackageUsage';
                        $usedCount = 0;
                        if (class_exists($usageClass, true)) {
                            $usedCount = $usageClass::where('package_id', $package->id)
                                ->where('user_id', auth('customer')->id())
                                ->where('status', 'used')
                                ->count();
                        }
                        $remaining = ($package->sessions_count ?? 0) - $usedCount;
                        $percentage = ($package->sessions_count ?? 0) > 0 ? ($usedCount / ($package->sessions_count ?? 1)) * 100 : 0;
                        $expiryDate = $package->created_at ? $package->created_at->addDays($package->validity_days ?? 0) : now();
                        $daysUntilExpiry = now()->diffInDays($expiryDate, false);
                    @endphp
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $package->name ?? translate('Unknown Package') }}</h6>
                                <p class="mb-0 text-muted small">
                                    {{ $package->salon->store->name ?? ($package->salon->name ?? translate('Unknown Salon')) }}
                                </p>
                            </div>
                            <span class="badge badge-{{ $daysUntilExpiry <= 30 ? 'warning' : 'success' }}">
                                {{ $remaining }} {{ translate('sessions left') }}
                            </span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ min(100, max(0, $percentage)) }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                {{ $usedCount }}/{{ $package->sessions_count ?? 0 }} {{ translate('used') }}
                            </small>
                            @if($daysUntilExpiry <= 30 && $daysUntilExpiry >= 0)
                                <small class="text-warning">
                                    <i class="tio-warning"></i> {{ translate('Expires in') }} {{ $daysUntilExpiry }} {{ translate('days') }}
                                </small>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="tio-package fs-1 text-muted"></i>
                <p class="text-muted mt-2">{{ translate('No active packages') }}</p>
                <a href="{{ route('beauty-booking.search') }}" class="btn btn-sm btn-primary">
                    {{ translate('Browse Packages') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endif

