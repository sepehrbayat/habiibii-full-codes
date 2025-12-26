@extends('layouts.admin.app')

@section('title', translate('Banner Ads'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/category.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.Banner_Ads_Management') }}
                            <span class="badge badge-soft-dark ml-2" id="itemCount">{{ $ads->total() ?? 0 }}</span>
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header justify-content-between gap-3 py-2 flex-wrap">
                <form action="" method="get" class="search-form flex-grow-1 max-w-450px">
                    <!-- Search -->
                    <div class="input-group input--group">
                        <input id="datatableSearch_" type="search" value="{{ request()?->search ?? null }}"
                               name="search" class="form-control"
                               placeholder="{{ translate('Search Salon') }}"
                               aria-label="{{ translate('messages.search') }}">
                        <button type="submit" class="btn btn--secondary bg--primary"><i class="tio-search"></i></button>
                    </div>
                    <!-- End Search -->
                </form>
                @if (request()->get('search'))
                    <button type="reset" class="btn btn--primary ml-2 location-reload-to-base"
                            data-url="{{ url()->full() }}">{{ translate('messages.reset') }}</button>
                @endif
                <div class="search--button-wrapper justify-content-end gap-20px">
                    <div class="select-item min--200">
                        <select name="ad_position" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="ad_position">
                            <option value="">{{ translate('All Positions') }}</option>
                            <option value="homepage" {{ request('ad_position') == 'homepage' ? 'selected' : '' }}>{{ translate('Homepage') }}</option>
                            <option value="category" {{ request('ad_position') == 'category' ? 'selected' : '' }}>{{ translate('Category') }}</option>
                            <option value="search_results" {{ request('ad_position') == 'search_results' ? 'selected' : '' }}>{{ translate('Search Results') }}</option>
                        </select>
                    </div>
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
                            <th class="border-0">{{translate('sl')}}</th>
                            <th class="border-0">{{ translate('ID') }}</th>
                            <th class="border-0">{{ translate('Salon') }}</th>
                            <th class="border-0">{{ translate('Position') }}</th>
                            <th class="border-0">{{ translate('Banner Image') }}</th>
                            <th class="border-0">{{ translate('Start Date') }}</th>
                            <th class="border-0">{{ translate('End Date') }}</th>
                            <th class="border-0">{{ translate('Amount Paid') }}</th>
                            <th class="border-0">{{ translate('Status') }}</th>
                            <th class="text-center border-0">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="set-rows">
                        @foreach($ads as $key=>$ad)
                            <tr>
                                <td>{{$key+$ads->firstItem()}}</td>
                                <td>{{ $ad->id }}</td>
                                <td><span class="text--title">{{ Str::limit($ad->salon->store->name ?? '', 20) }}</span></td>
                                <td><span class="text--title">{{ ucfirst(str_replace('_', ' ', $ad->ad_position ?? '')) }}</span></td>
                                <td>
                                    @if($ad->banner_image)
                                        <img src="{{ asset('storage/app/public/' . $ad->banner_image) }}" alt="Banner" style="max-width: 100px;">
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><span class="text--title">{{ $ad->start_date->format('Y-m-d') }}</span></td>
                                <td><span class="text--title">{{ $ad->end_date->format('Y-m-d') }}</span></td>
                                <td><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($ad->amount_paid) }}</span></td>
                                <td>
                                    <span class="badge badge-{{ $ad->status == 'active' ? 'success' : ($ad->status == 'expired' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($ad->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a href="javascript:" class="btn action-btn btn--warning btn-outline-warning" title="{{ translate('messages.view') }}">
                                            <i class="tio-visible-outlined"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($ads) !== 0)
            <hr>
            @endif
            <div class="page-area">
                {!! $ads->withQueryString()->links() !!}
            </div>
            @if(count($ads) === 0)
            <div class="empty--data">
                <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                <h5>
                    {{translate('no_data_found')}}
                </h5>
            </div>
            @endif
            <!-- End Table -->
        </div>
        <!-- End Card -->
    </div>
@endsection

