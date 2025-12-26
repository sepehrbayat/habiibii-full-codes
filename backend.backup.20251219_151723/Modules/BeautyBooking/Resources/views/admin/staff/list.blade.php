@extends('layouts.admin.app')

@section('title', translate('messages.staff_list'))


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/rental/veh.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.staff_list') }}</span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title text--title">
                        {{ translate('messages.Total_Staff') }}
                        <span class="badge badge-soft-dark ml-2 rounded-circle" id="itemCount">{{ $staffList->total() }}</span>
                    </h5>
                    <form class="search-form flex-grow-1 max-w-353px">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" value="{{ request()?->search ?? null }}"
                                   name="search" class="form-control"
                                   placeholder="{{ translate('Search by name, salon...') }}"
                                   aria-label="{{ translate('messages.Search by name, salon...') }}">
                            <button type="submit" class="btn btn--secondary bg--primary"><i
                                    class="tio-search"></i></button>

                        </div>
                        <!-- End Search -->
                    </form>
                    @if (request()->get('search'))
                        <button type="reset" class="btn btn--primary ml-2 location-reload-to-base"
                                data-url="{{ url()->full() }}">{{ translate('messages.reset') }}</button>
                    @endif


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
                            <a id="export-excel" class="dropdown-item"
                               href="{{ route('admin.beautybooking.staff.export', ['type' => 'excel', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ asset('public/assets/admin') }}/svg/components/excel.svg"
                                     alt="Image Description">
                                {{ translate('messages.excel') }}
                            </a>
                            <a id="export-csv" class="dropdown-item"
                               href="{{ route('admin.beautybooking.staff.export', ['type' => 'csv', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                     src="{{ asset('public/assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                     alt="Image Description">
                                .{{ translate('messages.csv') }}
                            </a>

                        </div>
                    </div>
                    <!-- End Unfold -->
                    <a class="btn btn--primary font-weight-bold float-right mr-2 mb-0"
                       href="{{ route('admin.beautybooking.staff.create') }}">{{ translate('messages.new_staff') }}
                    </a>
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
                        <th class="border-0">{{ translate('messages.Staff_Info') }}</th>
                        <th class="border-0">{{ translate('messages.Salon') }}</th>
                        <th class="border-0">{{ translate('messages.Specializations') }}</th>
                        <th class="border-0">{{ translate('messages.Total_Bookings') }}</th>
                        <th class="text-center border-0">{{ translate('messages.Staff_Status') }}</th>
                        <th class="text-center border-0">{{ translate('messages.Action') }}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($staffList as $key => $staff)
                        <tr>
                            <td>{{$key+$staffList->firstItem()}}</td>
                            <td>
                                <div class="text--title">
                                    <a href="{{ route('admin.beautybooking.staff.details', $staff->id)}}" class="font-medium">
                                        {{ $staff->name }}
                                    </a>
                                    <div class="opacity-lg">
                                        {{ $staff->email ?? 'N/A' }}
                                    </div>
                                    <div class="opacity-lg">
                                        {{ $staff->phone ?? 'N/A' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text--title">
                                    <a href="{{ route('admin.beautybooking.salon.view', $staff->salon_id)}}" class="font-medium">
                                        {{ $staff->salon->store->name ?? translate('salon_not_found') }}
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="text--title font-medium">
                                    {{ Str::limit(implode(', ', $staff->specializations ?? []), 30) ?: 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <div class="text--title font-medium">
                                    {{ $staff->bookings()->count() ?? 0 }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center">
                                    <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$staff->id}}">
                                        <input type="checkbox" data-url="{{route('admin.beautybooking.staff.status',[$staff['id'],$staff->status?0:1])}}"
                                               class="toggle-switch-input redirect-url" id="stocksCheckbox{{$staff->id}}" {{$staff->status?'checked':''}}>
                                        <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--warning btn-outline-warning" href="{{ route('admin.beautybooking.staff.details', $staff->id)}}"
                                       title="{{ translate('messages.view') }}"><i class="tio-visible-outlined"></i>
                                    </a>
                                    <a class="btn action-btn btn-outline-primary" href="{{ route('admin.beautybooking.staff.edit', $staff->id)}}"
                                       title="{{ translate('messages.edit') }}"><i class="tio-edit"></i>
                                    </a>
                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                       data-id="staff-{{$staff['id']}}" data-message="{{ translate('Want to delete this staff') }}" title="{{translate('messages.delete_staff')}}"><i
                                            class="tio-delete-outlined"></i>
                                    </a>

                                </div>
                                <form action="{{route('admin.beautybooking.staff.delete',[$staff['id']])}}" method="post" id="staff-{{$staff->id}}">
                                    @csrf @method('delete')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
            @if(count($staffList) !== 0)
                <hr>
            @endif
            <div class="page-area">
                {!! $staffList->appends($_GET)->links() !!}
            </div>
            @if(count($staffList) === 0)
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


@push('script_2')
@endpush

