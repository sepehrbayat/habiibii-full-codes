@extends('layouts.admin.app')

@section('title', translate('Review List'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/items.png')}}" class="w--22" alt="">
                </span>
                <span>
                    {{translate('messages.Reviews')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header border-0 py-2">
                <h5 class="card-title">
                    {{translate('messages.Review_list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$reviews->total()}}</span></h5>
                <div class="search--button-wrapper justify-content-end">
                    <form  class="search-form">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch" name="search" value="{{ request()?->search ?? null }}" type="search" class="form-control min-height-45" placeholder="{{translate('ex_:_search_salon_Name,_customer_Name,_Rating')}}" aria-label="{{translate('messages.search_here')}}">
                            <button type="submit" class="btn btn--secondary min-height-45"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>

                    @if(request()->get('search'))
                    <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{translate('messages.reset')}}</button>
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
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.beautybooking.review.export', array_merge(['type' => 'excel'], request()->query()))}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.beautybooking.review.export', array_merge(['type' => 'csv'], request()->query()))}}">
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
                                <th class="border-0">{{ translate('messages.User') }}</th>
                                <th class="border-0">{{ translate('messages.Salon') }}</th>
                                <th class="border-0">{{ translate('messages.Rating') }}</th>
                                <th class="border-0">{{ translate('messages.Comment') }}</th>
                                <th class="border-0">{{ translate('messages.status') }}</th>
                                <th class="text-center border-0">{{ translate('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach($reviews as $key=>$review)
                                <tr>
                                    <td>{{$key+$reviews->firstItem()}}</td>
                                    <td><span class="text--title">{{ ($review->user->f_name ?? '') . ' ' . ($review->user->l_name ?? '') }}</span></td>
                                    <td><span class="text--title">{{ Str::limit($review->salon->store->name ?? 'N/A', 20) }}</span></td>
                                    <td>
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="tio-star{{ $i <= $review->rating ? '' : '-outlined' }} text-warning"></i>
                                        @endfor
                                    </td>
                                    <td><span class="text--title">{{ Str::limit($review->comment ?? 'N/A', 50) }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($review->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--warning btn-outline-warning"
                                                    href="{{route('admin.beautybooking.review.view', $review->id)}}"
                                                    title="{{ translate('messages.details') }}"><i
                                                        class="tio-visible-outlined"></i>
                                                </a>
                                            @if($review->status == 'pending')
                                                <form action="{{ route('admin.beautybooking.review.approve', $review->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn action-btn btn--success btn-outline-success" title="{{ translate('Approve') }}">
                                                        <i class="tio-checkmark"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.beautybooking.review.reject', $review->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn action-btn btn--danger btn-outline-danger" title="{{ translate('Reject') }}">
                                                        <i class="tio-close"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($reviews) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $reviews->withQueryString()->links() !!}
                </div>
                @if(count($reviews) === 0)
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


