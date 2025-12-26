<div class="mb-4">
    <h5 class="mb-3">{{ translate('Select Service') }} <span class="text-danger">*</span></h5>
    
    @if($services && $services->count() > 0)
        <form id="service-selection-form" method="POST" action="{{ route('beauty-booking.booking.step.save', ['step' => 1]) }}">
            @csrf
            <input type="hidden" name="salon_id" value="{{ $salon->id }}">
            
            <div class="list-group mb-3">
                @foreach($services as $service)
                    <label class="list-group-item list-group-item-action cursor-pointer" style="cursor: pointer;">
                        <div class="d-flex align-items-start">
                            <input type="radio" name="service_id" value="{{ $service->id }}" class="mt-2 me-3 service-radio" required>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="d-block">{{ $service->name }}</strong>
                                        @if($service->category)
                                            <span class="badge badge-info mt-1">{{ $service->category->name }}</span>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-primary">{{ number_format($service->price, 2) }} {{ getCurrencySymbol() }}</strong>
                                    </div>
                                </div>
                                @if($service->description)
                                    <p class="text-muted small mt-2 mb-1">{{ Str::limit($service->description, 100) }}</p>
                                @endif
                                <div class="d-flex align-items-center mt-2">
                                    <i class="tio-time me-1"></i>
                                    <small class="text-muted">{{ $service->duration_minutes }} {{ translate('minutes') }}</small>
                                    @if($service->image)
                                        <span class="ms-3">
                                            <img src="{{ asset('storage/app/public/' . $service->image) }}" 
                                                 alt="{{ $service->name }}" 
                                                 class="rounded" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('beauty-booking.salon.show', $salon->id) }}" class="btn btn-secondary">
                    <i class="tio-arrow-backward"></i> {{ translate('Back') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ translate('Next') }} <i class="tio-arrow-forward"></i>
                </button>
            </div>
        </form>
    @else
        <div class="alert alert-warning">
            <i class="tio-warning"></i> {{ translate('No services available for this salon at the moment.') }}
        </div>
        <a href="{{ route('beauty-booking.salon.show', $salon->id) }}" class="btn btn-secondary">
            <i class="tio-arrow-backward"></i> {{ translate('Back to Salon') }}
        </a>
    @endif
</div>

