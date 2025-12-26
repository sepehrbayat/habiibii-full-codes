@extends('layouts.admin.app')

@section('title', translate('Loyalty Campaigns'))

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
                            <img src="{{ asset('public/assets/admin/img/beauty/loyalty.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.Loyalty_Campaigns') }}
                            <span class="badge badge-soft-dark ml-2" id="itemCount">{{ $campaigns->total() }}</span>
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats Cards -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="resturant-card card--bg-1">
                    <h4 class="title">{{ number_format($totalPointsIssued ?? 0, 0) }}</h4>
                    <span class="subtitle">{{ translate('messages.total_points_issued') }}</span>
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/total_provider.png')}}" alt="points">
                </div>
            </div>
            <div class="col-md-4">
                <div class="resturant-card card--bg-2">
                    <h4 class="title">{{ number_format($totalPointsRedeemed ?? 0, 0) }}</h4>
                    <span class="subtitle">{{ translate('messages.total_points_redeemed') }}</span>
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/new_provider.png')}}" alt="redeemed">
                </div>
            </div>
            <div class="col-md-4">
                <div class="resturant-card card--bg-3">
                    <h4 class="title">{{ $activeCampaigns ?? 0 }}</h4>
                    <span class="subtitle">{{ translate('messages.active_campaigns') }}</span>
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/active_provider.png')}}" alt="campaigns">
                </div>
            </div>
        </div>

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header justify-content-between gap-3 py-2 flex-wrap">
                <form action="" method="get" class="search-form flex-grow-1 max-w-450px">
                    <!-- Search -->
                    <div class="input-group input--group">
                        <input id="datatableSearch_" type="search" value="{{ request()?->search ?? null }}"
                               name="search" class="form-control"
                               placeholder="{{ translate('ex_:_Search_campaign') }}"
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
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.beautybooking.loyalty.export', array_merge(['type' => 'excel'], request()->query()))}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                     alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.beautybooking.loyalty.export', array_merge(['type' => 'csv'], request()->query()))}}">
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
                    <select name="salon_id" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="salon_id">
                        <option value="" {{!request('salon_id')?'selected':''}}>{{ translate('messages.All_Salons') }}</option>
                        @foreach($salons ?? [] as $salon)
                            <option value="{{ $salon->id }}" {{request('salon_id') == $salon->id?'selected':''}}>
                                {{ $salon->store->name ?? 'Salon #' . $salon->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="select-item min--280 mb-3">
                    <select name="status" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="status">
                        <option value="" {{!request('status')?'selected':''}}>{{ translate('messages.All_Status') }}</option>
                        <option value="active" {{request('status') == 'active'?'selected':''}}>{{ translate('messages.Active') }}</option>
                        <option value="inactive" {{request('status') == 'inactive'?'selected':''}}>{{ translate('messages.Inactive') }}</option>
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
                                <th class="border-0">{{ translate('messages.Campaign_Name') }}</th>
                                <th class="border-0">{{ translate('messages.Salon') }}</th>
                                <th class="border-0">{{ translate('messages.Type') }}</th>
                                <th class="border-0">{{ translate('messages.Start_Date') }}</th>
                                <th class="border-0">{{ translate('messages.End_Date') }}</th>
                                <th class="border-0">{{ translate('messages.Participants') }}</th>
                                <th class="border-0">{{ translate('messages.status') }}</th>
                                <th class="text-center border-0">{{ translate('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach($campaigns as $key=>$campaign)
                                <tr>
                                    <td>{{$key+$campaigns->firstItem()}}</td>
                                    <td><span class="text--title">{{ $campaign->name }}</span></td>
                                    <td><span class="text--title">{{ Str::limit($campaign->salon->store->name ?? translate('Platform-wide'), 20) }}</span></td>
                                    <td><span class="text--title">{{ ucfirst($campaign->type) }}</span></td>
                                    <td><span class="text--title">{{ $campaign->start_date->format('Y-m-d') }}</span></td>
                                    <td><span class="text--title">{{ $campaign->end_date ? $campaign->end_date->format('Y-m-d') : translate('No End Date') }}</span></td>
                                    <td><span class="text--title">{{ $campaign->total_participants }}</span></td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="statusCheckbox{{$campaign->id}}">
                                            <input type="checkbox" data-url="{{route('admin.beautybooking.loyalty.status',[$campaign->id])}}" class="toggle-switch-input status_change_alert" id="statusCheckbox{{$campaign->id}}" {{$campaign->is_active?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a href="javascript:" class="btn action-btn btn--warning btn-outline-warning">
                                                <i class="tio-visible-outlined"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($campaigns) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $campaigns->withQueryString()->links() !!}
                </div>
                @if(count($campaigns) === 0)
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

