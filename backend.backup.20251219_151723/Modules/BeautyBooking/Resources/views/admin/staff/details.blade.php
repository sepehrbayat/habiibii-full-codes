@extends('layouts.admin.app')

@section('title', translate('messages.Staff_Details'))

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
                        <span>{{ translate('messages.Staff_Details') }}
                    </h1>
                </div>
                <div class="d-flex align-items-start flex-wrap gap-2">
                    <a class="btn btn--cancel h--45px d-flex gap-2 align-items-center form-alert" href="javascript:"
                        data-id="staff-{{ $staff['id'] }}" data-message="{{ translate('Want to delete this staff') }}"
                        title="{{ translate('messages.delete_staff') }}">
                        <i class="tio-delete-outlined"></i>
                        {{ translate('messages.delete') }}
                    </a>
                    <form action="{{ route('admin.beautybooking.staff.delete', [$staff['id']]) }}" method="post"
                        id="staff-{{ $staff['id'] }}">
                        @csrf @method('delete')
                    </form>
                    <a href="javascript:"
                        class="btn btn--reset d-flex justify-content-between align-items-center gap-4 lh--1 h--45px">
                        {{ translate('messages.status') }}
                        <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{ $staff->id }}">
                            <input type="checkbox"
                                data-url="{{ route('admin.beautybooking.staff.status', [$staff['id'], $staff->status ? 0 : 1]) }}"
                                class="toggle-switch-input redirect-url" id="stocksCheckbox{{ $staff->id }}"
                                {{ $staff->status ? 'checked' : '' }}>
                            <span class="toggle-switch-label mx-auto">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </a>
                    <a href="{{ route('admin.beautybooking.staff.edit', $staff->id) }}"
                        class="btn btn--primary h--45px d-flex gap-2 align-items-center">
                        <i class="tio-edit"></i>
                        {{ translate('messages.Edit_Staff') }}
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card mb-20">
            <div class="card-body p-4">
                <div class="card border p-3 p-sm-4 shadow-none mb-3">
                    <div class="media align-items-sm-center flex-column flex-sm-row">
                        <div class="mb-3 mb-sm-0">
                            <img height="115" class="aspect-ratio-1 w-auto rounded mr-4 onerror-image"
                                src="{{ $staff->avatar_full_url ?? asset('public/assets/admin/img/100x100/1.png') }}" alt="">
                        </div>
                        <div class="media-body text--title d-flex justify-content-around flex-column flex-lg-row gap-3">
                            <div class="mr-0 mr-lg-4">
                                <h3 class="fs-20 mb-0">{{ $staff->name }}</h3>
                                <div class="d-flex gap-3">
                                    <span class="min-w-110px">{{ translate('Phone') }}</span>
                                    <span>: {{ $staff->phone ?? 'N/A' }}</span>
                                </div>
                                <div class="d-flex gap-3">
                                    <span class="min-w-110px">{{ translate('Email') }}</span>
                                    <span>: {{ $staff->email ?? 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="mr-0 mr-lg-4">
                                <h5 class="">{{ translate('Salon Information') }}</h5>
                                <div class="align-items-center d-flex gap-2 resturant--information-single text-left">
                                    <img height="45" class="aspect-ratio-1 onerror-image rounded"
                                        src="{{ $staff->salon->store->logo_full_url ?? asset('public/assets/admin/img/100x100/1.png') }}"
                                        alt="{{ translate('Image Description') }}">
                                    <div class="text--title">
                                        <a class="media align-items-center deco-none resturant--information-single"
                                            href="{{ isset($staff->salon) ? route('admin.beautybooking.salon.view', $staff->salon->id) : '#' }}">
                                            <h5 class="text-capitalize font-semibold text-hover-primary d-block mb-1">
                                                {{ $staff->salon->store->name ?? 'N/A' }}
                                            </h5>
                                        </a>
                                        <span class="opacity-lg">
                                            {{ $staff->salon->store->phone ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            @if($staff->specializations && count($staff->specializations) > 0)
                            <div class="mr-0 mr-lg-4">
                                <h5 class="">{{ translate('Specializations') }}</h5>
                                <div class="d-flex gap-3 flex-wrap">
                                    @foreach($staff->specializations as $spec)
                                        <span class="badge badge-soft-info">{{ $spec }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Card -->

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
                            <th class="border-0">{{ translate('messages.Service_Info') }}</th>
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
                                        {{ $booking->service->name ?? 'N/A' }}
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

