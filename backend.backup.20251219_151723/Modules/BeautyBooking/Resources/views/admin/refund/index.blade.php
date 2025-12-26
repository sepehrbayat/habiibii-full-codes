@extends('layouts.admin.app')

@section('title', translate('messages.refund_requests'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/zone.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.refund_requests') }}</span>
                    </h1>
                </div>
            </div>
            <div class="mt-3">
                <ul class="nav nav-pills">
                    @php
                        $refundTabs = [
                            'all' => translate('messages.All'),
                            'refund_pending' => translate('messages.refund_pending'),
                            'refunded' => translate('messages.refunded'),
                            'partial_refund' => translate('messages.partial_refund'),
                        ];
                    @endphp
                    @foreach ($refundTabs as $key => $label)
                        <li class="nav-item mr-2 mb-2">
                            <a class="nav-link {{ $refundStatus === $key ? 'active' : '' }}"
                               href="{{ route('admin.beautybooking.refund.list', array_merge(request()->except('page'), ['refund_status' => $key])) }}">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title text--title">
                        {{ translate('messages.refund_requests') }}
                        <span class="badge badge-soft-dark ml-2 rounded-circle" id="itemCount">{{ $bookings->total() }}</span>
                    </h5>
                    <form action="" method="get" class="search-form flex-grow-1 max-w-353px">
                        <input type="hidden" value="{{ request()?->refund_status }}" name="refund_status">
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" value="{{ request()?->search ?? null }}"
                                   name="search" class="form-control"
                                   placeholder="{{ translate('Search by booking reference, customer, salon') }}"
                                   aria-label="{{ translate('messages.Search by booking reference, customer name, email') }}">
                            <button type="submit" class="btn btn--secondary bg--primary"><i class="tio-search"></i></button>
                        </div>
                    </form>
                    @if (request()->get('search'))
                        <button type="reset" class="btn btn--primary ml-2 location-reload-to-base"
                                data-url="{{ url()->current() }}">{{ translate('messages.reset') }}</button>
                    @endif
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('sl') }}</th>
                        <th>{{ translate('messages.Reference') }}</th>
                        <th>{{ translate('messages.Customer') }}</th>
                        <th>{{ translate('messages.Salon') }}</th>
                        <th>{{ translate('messages.Amount') }}</th>
                        <th>{{ translate('messages.status') }}</th>
                        <th>{{ translate('messages.Payment') }}</th>
                        <th class="text-center">{{ translate('messages.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($bookings as $key => $booking)
                        <tr>
                            <td>{{ $key + $bookings->firstItem() }}</td>
                            <td>{{ $booking->booking_reference }}</td>
                            <td>{{ ($booking->user->f_name ?? '') . ' ' . ($booking->user->l_name ?? '') }}</td>
                            <td>{{ $booking->salon->store->name ?? 'N/A' }}</td>
                            <td>{{ \App\CentralLogics\Helpers::format_currency($booking->total_amount) }}</td>
                            <td>
                                <span class="badge badge-{{ $booking->status === 'cancelled' ? 'danger' : ($booking->status === 'pending' ? 'warning' : 'info') }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $booking->payment_status === 'refunded' ? 'success' : ($booking->payment_status === 'refund_pending' ? 'warning' : 'info') }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a class="btn btn-sm btn--primary" href="{{ route('admin.beautybooking.booking.view', $booking->id) }}">
                                    <i class="tio-visible"></i> {{ translate('messages.view') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if ($bookings->isEmpty())
                <div class="empty--data">
                    <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                    <h5>{{ translate('no_data_found') }}</h5>
                </div>
            @else
                <div class="page-area">
                    {!! $bookings->appends(request()->query())->links() !!}
                </div>
            @endif
        </div>
    </div>
@endsection

