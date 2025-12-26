@extends('layouts.admin.app')

@section('title', translate('messages.service_details'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/car-logo.png') }}" alt="">
                        </span>
                        <span>{{ $service->name }}
                    </h1>
                </div>
                <div class="d-flex align-items-start flex-wrap gap-2">
                    <a class="btn btn--cancel h--45px d-flex gap-2 align-items-center form-alert" href="javascript:"
                       data-id="service-{{$service['id']}}" data-message="{{ translate('Want to delete this service') }}" title="{{translate('messages.delete_service')}}">
                        <i class="tio-delete-outlined"></i>
                        {{ translate('messages.delete') }}
                    </a>
                    <form action="{{route('admin.beautybooking.service.delete',[$service['id']])}}?service_list={{request()->service_list}}&salon_id={{request()->salon_id}}&service_list={{request()->service_list}}" method="post" id="service-{{$service->id}}">
                        @csrf @method('delete')
                    </form>
                    <a href="javascript:" class="btn btn--reset d-flex justify-content-between align-items-center gap-4 lh--1 h--45px">
                        {{ translate('messages.status') }}
                        <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$service->id}}">
                            <input type="checkbox" data-url="{{route('admin.beautybooking.service.status',[$service['id'],$service->status?0:1])}}"
                                   class="toggle-switch-input redirect-url" id="stocksCheckbox{{$service->id}}" {{$service->status?'checked':''}}>
                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                        </label>
                    </a>
                    <a href="{{ route('admin.beautybooking.service.edit', $service->id)}}" class="btn btn--primary h--45px d-flex gap-2 align-items-center">
                        <i class="tio-edit"></i>
                        {{ translate('messages.Edit_Service') }}
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        
        <!-- Relations -->
        <div class="card mb-20">
            <div class="card-header py-2">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title text--title mb-0">
                        {{ translate('messages.service_relations') }}
                    </h5>
                    <a class="btn btn-sm btn--primary" href="{{ route('admin.beautybooking.service-relations.list') }}">
                        {{ translate('messages.manage') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('messages.related_service') }}</th>
                                <th>{{ translate('messages.relation_type') }}</th>
                                <th>{{ translate('messages.priority') }}</th>
                                <th>{{ translate('messages.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($service->activeRelations as $relation)
                                <tr>
                                    <td>
                                        <span class="text--title">{{ $relation->relatedService?->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-info text-capitalize">
                                            {{ str_replace('_', ' ', $relation->relation_type) }}
                                        </span>
                                    </td>
                                    <td>{{ $relation->priority }}</td>
                                    <td>
                                        <span class="badge badge-{{ $relation->status ? 'success' : 'secondary' }}">
                                            {{ $relation->status ? translate('messages.active') : translate('messages.inactive') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        {{ translate('messages.no_relations_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Relations -->

        <div class="card mb-20">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        @if($service->image)
                            <div class="cz-product-gallery mb-20 mb-lg-0">
                                <div class="cz-preview">
                                    <div class="product-preview-item d-flex align-items-center justify-content-center active">
                                        <img class="img-responsive w-100 rounded"
                                             src="{{ asset('storage/app/public/' . $service->image) }}"
                                             alt="Service Image">
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center p-4 border rounded">
                                <img src="{{ asset('public/assets/admin/img/100x100/1.png') }}" alt="No Image" class="img-fluid">
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-8">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <h3 class="fs-20 mb-2">{{ $service->name }}</h3>
                                <p class="text-muted">{{ $service->description ?? 'N/A' }}</p>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex gap-3">
                                        <span class="min-w-110px font-semibold">{{ translate('messages.Salon') }}</span>
                                        <span>:</span>
                                        <a href="{{ route('admin.beautybooking.salon.view', $service->salon_id)}}" class="text-primary">
                                            {{ $service->salon->store->name ?? 'N/A' }}
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-3">
                                        <span class="min-w-110px font-semibold">{{ translate('messages.Category') }}</span>
                                        <span>:</span>
                                        <span>{{ $service->category->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-3">
                                        <span class="min-w-110px font-semibold">{{ translate('messages.Duration') }}</span>
                                        <span>:</span>
                                        <span>{{ $service->duration_minutes }} {{ translate('messages.minutes') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-3">
                                        <span class="min-w-110px font-semibold">{{ translate('messages.Price') }}</span>
                                        <span>:</span>
                                        <span class="font-bold">{{ \App\CentralLogics\Helpers::format_currency($service->price) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-3">
                                        <span class="min-w-110px font-semibold">{{ translate('messages.Status') }}</span>
                                        <span>:</span>
                                        <span class="badge badge-{{ $service->status ? 'success' : 'danger' }}">
                                            {{ $service->status ? translate('messages.Active') : translate('messages.Inactive') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-3">
                                        <span class="min-w-110px font-semibold">{{ translate('messages.Total_Bookings') }}</span>
                                        <span>:</span>
                                        <span>{{ $service->bookings()->count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2">
                <div class="search--button-wrapper gap-20px">
                    <h5 class="card-title text--title flex-grow-1">{{ translate('messages.Total_Bookings') }}
                        <span class="badge badge-soft-dark ml-2" id="itemCount">{{ $bookings->total() }}</span>
                    </h5>
                    <form class="search-form flex-grow-1 max-w-353px">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" value="{{ request()?->search ?? null }}"
                                name="search" class="form-control" placeholder="{{ translate('Search by booking ID') }}"
                                aria-label="{{ translate('messages.Search by booking ID...') }}">
                            <button type="submit" class="btn btn--secondary bg--primary"><i
                                    class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>
                    @if (request()->get('search'))
                        <button type="reset" class="btn btn--primary ml-2 location-reload-to-base"
                            data-url="{{ url()->full() }}">{{ translate('messages.reset') }}</button>
                    @endif
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table id="columnSearchDatatable"
                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                    data-hs-datatables-options='{
                        "order": [],
                        "orderCellsTop": true,
                        "paging":false
                    }'>
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0">{{ translate('sl') }}</th>
                            <th class="border-0">{{ translate('messages.Booking ID') }}</th>
                            <th class="border-0">{{ translate('messages.Booking_Date') }}</th>
                            <th class="border-0">{{ translate('messages.Customer_Info') }}</th>
                            <th class="border-0">{{ translate('messages.Total_Amount') }}</th>
                            <th class="text-center border-0">{{ translate('messages.Booking_Status') }}</th>
                            <th class="text-center border-0">{{ translate('messages.Action') }}</th>
                        </tr>
                    </thead>

                    <tbody id="set-rows">
                        @foreach ($bookings as $key => $booking)
                            <tr>
                                <td>{{ $key + $bookings->firstItem() }}</td>
                                <td>
                                    <a href="{{ route('admin.beautybooking.booking.view', $booking->id) }}"
                                        target="_blank" rel="noopener noreferrer">
                                        <div class="text--title font-semibold">
                                            #{{ $booking->id }}
                                        </div>
                                    </a>
                                </td>
                                <td>
                                    <div class="text--title">
                                        {{ \App\CentralLogics\Helpers::date_format($booking->booking_date) }}
                                        <br>
                                        {{ $booking->booking_time }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text--title">
                                        @if ($booking->user)
                                            <a href="{{ route('admin.users.customer.view', $booking->user_id) }}" target="_blank" rel="noopener noreferrer">
                                                <div class="font-medium">
                                                    {{ $booking->user->f_name }} {{ $booking->user->l_name }}
                                                </div>
                                            </a>
                                            <div class="opacity-lg">
                                                {{ $booking->user->phone }}
                                            </div>
                                        @else
                                            {{ translate('messages.Guest_user') }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="text--title font-medium">
                                        {{ \App\CentralLogics\Helpers::format_currency($booking->total_amount) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        @php
                                            $statusClasses = [
                                                'pending' => 'badge-soft-info',
                                                'confirmed' => 'badge-soft-success',
                                                'completed' => 'badge-soft-success',
                                                'cancelled' => 'badge-soft-danger',
                                            ];
                                            $badgeClass = $statusClasses[$booking->status] ?? 'badge-soft-info';
                                        @endphp
                                        <label class="badge {{ $badgeClass }} border-0">
                                            {{ translate($booking->status) }}
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn action-btn btn--primary btn-outline-primary"
                                            href="{{ route('admin.beautybooking.booking.view', $booking->id) }}"
                                            title="{{ translate('messages.view') }}"><i class="tio-visible-outlined"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if (count($bookings) !== 0)
                <hr>
            @endif
            <div class="page-area">
                {!! $bookings->appends($_GET)->links() !!}
            </div>
            @if (count($bookings) === 0)
                <div class="empty--data">
                    <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                    <h5>
                        {{ translate('no_data_found') }}
                    </h5>
                </div>
            @endif
            <!-- End Table -->
        </div>
        <!-- End Card -->
    </div>
@endsection

@push('script_2')
@endpush

