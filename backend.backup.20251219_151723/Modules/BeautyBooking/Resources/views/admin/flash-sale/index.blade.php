@extends('layouts.admin.app')

@section('title', translate('messages.flash_sale'))

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
                        <span>{{ translate('messages.flash_sale') }}</span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title text--title">
                        {{ translate('messages.flash_sale_list') }}
                        <span class="badge badge-soft-dark ml-2 rounded-circle" id="itemCount">{{ $flashSales->total() }}</span>
                    </h5>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('sl') }}</th>
                        <th>{{ translate('messages.title') }}</th>
                        <th>{{ translate('messages.start_date') }}</th>
                        <th>{{ translate('messages.end_date') }}</th>
                        <th>{{ translate('messages.admin_discount') }}</th>
                        <th>{{ translate('messages.vendor_discount') }}</th>
                        <th>{{ translate('messages.status') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($flashSales as $key => $sale)
                        <tr>
                            <td>{{ $key + $flashSales->firstItem() }}</td>
                            <td>{{ $sale->title ?? '-' }}</td>
                            <td>{{ optional($sale->start_date)->format('Y-m-d H:i') }}</td>
                            <td>{{ optional($sale->end_date)->format('Y-m-d H:i') }}</td>
                            <td>{{ $sale->admin_discount_percentage }}%</td>
                            <td>{{ $sale->vendor_discount_percentage }}%</td>
                            <td>
                                <span class="badge badge-{{ $sale->is_publish ? 'success' : 'secondary' }}">
                                    {{ $sale->is_publish ? translate('messages.published') : translate('messages.unpublished') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if ($flashSales->isEmpty())
                <div class="empty--data">
                    <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                    <h5>{{ translate('no_data_found') }}</h5>
                </div>
            @else
                <div class="page-area">
                    {!! $flashSales->appends(request()->query())->links() !!}
                </div>
            @endif
        </div>
    </div>
@endsection

