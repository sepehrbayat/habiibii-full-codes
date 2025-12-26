@extends('layouts.admin.app')

@section('title', translate('Subscriptions'))

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
                            <img src="{{ asset('public/assets/admin/img/beauty/subscription.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.Subscriptions') }}
                            <span class="badge badge-soft-dark ml-2" id="itemCount">{{ $subscriptions->total() }}</span>
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
                               placeholder="{{ translate('ex_:_Search_salon') }}"
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
                    <!-- Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40 font-semibold"
                           href="javascript:;"
                           data-hs-unfold-options='{
                                "target": "#usersExportDropdown",
                                "type": "css-animation"
                            }'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="usersExportDropdown"
                             class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.beautybooking.subscription.export', array_merge(['type' => 'excel'], request()->query()))}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                     alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.beautybooking.subscription.export', array_merge(['type' => 'csv'], request()->query()))}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ asset('public/assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                     alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>

                        </div>
                    </div>
                    <!-- End Unfold -->
                </div>
            </div>
            <!-- End Header -->
            <div class="card-body">
                @if(!isset(auth('admin')->user()->zone_id))
                <div class="select-item min--280 mb-3">
                    <select name="subscription_type" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="subscription_type">
                        <option value="" {{!request('subscription_type')?'selected':''}}>{{ translate('messages.All_Types') }}</option>
                        <option value="featured" {{request('subscription_type') == 'featured'?'selected':''}}>{{ translate('messages.Featured') }}</option>
                        <option value="boost" {{request('subscription_type') == 'boost'?'selected':''}}>{{ translate('messages.Boost') }}</option>
                        <option value="banner_homepage" {{request('subscription_type') == 'banner_homepage'?'selected':''}}>{{ translate('messages.Homepage_Banner') }}</option>
                        <option value="banner_category" {{request('subscription_type') == 'banner_category'?'selected':''}}>{{ translate('messages.Category_Banner') }}</option>
                        <option value="banner_search_results" {{request('subscription_type') == 'banner_search_results'?'selected':''}}>{{ translate('messages.Search_Results_Banner') }}</option>
                        <option value="advanced_dashboard" {{request('subscription_type') == 'advanced_dashboard'?'selected':''}}>{{ translate('messages.Advanced_Dashboard') }}</option>
                    </select>
                </div>
                <div class="select-item min--280 mb-3">
                    <select name="status" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="status">
                        <option value="" {{!request('status')?'selected':''}}>{{ translate('messages.All_Status') }}</option>
                        <option value="active" {{request('status') == 'active'?'selected':''}}>{{ translate('messages.Active') }}</option>
                        <option value="expired" {{request('status') == 'expired'?'selected':''}}>{{ translate('messages.Expired') }}</option>
                        <option value="pending" {{request('status') == 'pending'?'selected':''}}>{{ translate('messages.Pending') }}</option>
                    </select>
                </div>
                @endif
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
                                <th class="border-0">{{ translate('messages.Salon') }}</th>
                                <th class="border-0">{{ translate('messages.Type') }}</th>
                                <th class="border-0">{{ translate('messages.Start_Date') }}</th>
                                <th class="border-0">{{ translate('messages.End_Date') }}</th>
                                <th class="border-0">{{ translate('messages.Amount_Paid') }}</th>
                                <th class="border-0">{{ translate('messages.status') }}</th>
                                <th class="text-center border-0">{{ translate('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach($subscriptions as $key=>$subscription)
                                <tr>
                                    <td>{{$key+$subscriptions->firstItem()}}</td>
                                    <td><span class="text--title">{{ Str::limit($subscription->salon->store->name ?? 'N/A', 20) }}</span></td>
                                    <td><span class="text--title">{{ ucfirst(str_replace('_', ' ', $subscription->subscription_type)) }}</span></td>
                                    <td><span class="text--title">{{ $subscription->start_date->format('Y-m-d') }}</span></td>
                                    <td><span class="text--title">{{ $subscription->end_date->format('Y-m-d') }}</span></td>
                                    <td><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($subscription->amount_paid) }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ $subscription->status == 'active' ? 'success' : ($subscription->status == 'expired' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            @if($subscription->salon)
                                                <a href="{{route('admin.beautybooking.salon.view', $subscription->salon->id)}}" 
                                                   class="btn action-btn btn--warning btn-outline-warning"
                                                   title="{{ translate('messages.view_salon') }}">
                                                    <i class="tio-visible-outlined"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($subscriptions) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $subscriptions->withQueryString()->links() !!}
                </div>
                @if(count($subscriptions) === 0)
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

