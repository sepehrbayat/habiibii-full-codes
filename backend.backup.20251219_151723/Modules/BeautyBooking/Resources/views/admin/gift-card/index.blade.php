@extends('layouts.admin.app')

@section('title', translate('Gift Card List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('public/assets/admin/img/items.png') }}" class="w--22" alt="">
                </span>
                <span>
                    {{translate('messages.Gift_Card_List')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{translate('messages.Gift_Card_List')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$giftCards->total()}}</span></h5>
                    <form class="search-form">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch" name="search" value="{{ request()?->search ?? null }}" type="search" class="form-control min-height-45" placeholder="{{translate('ex_:_Search_gift_card')}}" aria-label="{{translate('messages.search_here')}}">
                            <button type="submit" class="btn btn--secondary min-height-45"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>

                    @if(request()->get('search'))
                    <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{translate('messages.reset')}}</button>
                    @endif

                    @if(!isset(auth('admin')->user()->zone_id))
                    <div class="select-item min--280">
                        <select name="status" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="status">
                            <option value="" {{!request('status')?'selected':''}}>{{ translate('messages.All_Status') }}</option>
                            <option value="active" {{request('status') == 'active'?'selected':''}}>{{ translate('messages.Active') }}</option>
                            <option value="redeemed" {{request('status') == 'redeemed'?'selected':''}}>{{ translate('messages.Redeemed') }}</option>
                            <option value="expired" {{request('status') == 'expired'?'selected':''}}>{{ translate('messages.Expired') }}</option>
                        </select>
                    </div>
                    <div class="select-item min--280">
                        <select name="salon_id" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="salon_id">
                            <option value="" {{!request('salon_id')?'selected':''}}>{{ translate('messages.All_Salons') }}</option>
                            @foreach($salons ?? [] as $salon)
                                <option value="{{ $salon->id }}" {{request('salon_id') == $salon->id?'selected':''}}>
                                    {{ $salon->store->name ?? 'Salon #' . $salon->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40" href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-download-to mr-1"></i> {{ translate('messages.export') }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                            <span class="dropdown-header">{{ translate('messages.download_options') }}</span>
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.beautybooking.gift-card.export', array_merge(['type' => 'excel'], request()->query()))}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.beautybooking.gift-card.export', array_merge(['type' => 'csv'], request()->query()))}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>

                        </div>
                    </div>
                </div>
            </div>
            <!-- End Header -->
            <div class="card-body p-0">
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
                                <th class="border-0">{{ translate('messages.Code') }}</th>
                                <th class="border-0">{{ translate('messages.Amount') }}</th>
                                <th class="border-0">{{ translate('messages.Salon') }}</th>
                                <th class="border-0">{{ translate('messages.Purchased_By') }}</th>
                                <th class="border-0">{{ translate('messages.Redeemed_By') }}</th>
                                <th class="border-0">{{ translate('messages.status') }}</th>
                                <th class="border-0">{{ translate('messages.Expires_At') }}</th>
                                <th class="text-center border-0">{{ translate('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach($giftCards as $key=>$giftCard)
                                <tr>
                                    <td>{{$key+$giftCards->firstItem()}}</td>
                                    <td><code class="text--title">{{ $giftCard->code }}</code></td>
                                    <td><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($giftCard->amount) }}</span></td>
                                    <td><span class="text--title">{{ Str::limit($giftCard->salon->store->name ?? translate('General'), 20) }}</span></td>
                                    <td><span class="text--title">{{ ($giftCard->purchaser->f_name ?? '') . ' ' . ($giftCard->purchaser->l_name ?? '') }}</span></td>
                                    <td><span class="text--title">{{ $giftCard->redeemed_by ? (($giftCard->redeemer->f_name ?? '') . ' ' . ($giftCard->redeemer->l_name ?? '')) : '-' }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ $giftCard->status == 'active' ? 'success' : ($giftCard->status == 'redeemed' ? 'info' : 'danger') }}">
                                            {{ ucfirst($giftCard->status) }}
                                        </span>
                                    </td>
                                    <td><span class="text--title">{{ $giftCard->expires_at ? $giftCard->expires_at->format('Y-m-d') : '-' }}</span></td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a href="{{route('admin.beautybooking.gift-card.view', $giftCard->id)}}" class="btn action-btn btn--warning btn-outline-warning" title="{{ translate('messages.details') }}">
                                                <i class="tio-visible-outlined"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($giftCards) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $giftCards->withQueryString()->links() !!}
                </div>
                @if(count($giftCards) === 0)
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

