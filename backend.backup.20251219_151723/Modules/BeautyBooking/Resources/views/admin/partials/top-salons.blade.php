@forelse($topSalons as $salon)
    <a class="grid--card" href="{{ route('admin.beautybooking.salon.view', $salon->id)}}">
        <img class="onerror-image"
             data-onerror-image="{{ asset('public/assets/admin/img/160x160/img1.jpg') }}"
             src="{{ $salon->store['logo_full_url'] ?? asset('public/assets/admin/img/160x160/img1.jpg') }}">
        <div class="cont pt-2">
            <h6 class="mb-1">{{ $salon->store->name ?? 'Unknown' }}</h6>
            <span>{{ $salon->store->phone ?? '-' }}</span>
        </div>
        <div class="ml-auto">
            <span class="badge badge-soft">{{ translate('Bookings') }} : {{ $salon->booking_count ?? 0 }}</span>
        </div>
    </a>
@empty
    <div class="empty--data">
        <img src="{{ asset('/public/assets/admin/svg/illustrations/empty-state.svg') }}" alt="public">
        <h5>
            {{ translate('no_data_found') }}
        </h5>
    </div>
@endforelse
