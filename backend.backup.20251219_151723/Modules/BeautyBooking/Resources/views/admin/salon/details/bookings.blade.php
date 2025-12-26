<!-- Bookings Tab -->
<div class="card">
    <div class="card-header justify-content-between gap-3 py-2 flex-wrap">
        <form action="" method="get" class="search-form flex-grow-1 max-w-450px">
            <!-- Search -->
            <div class="input-group input--group">
                <input id="datatableSearch_" type="search" value="{{ request()?->search ?? null }}" name="search" class="form-control"
                        placeholder="{{translate('ex_:_Search_booking_reference')}}" aria-label="{{translate('messages.search')}}" >
                <button type="submit" class="btn btn--secondary bg--primary"><i class="tio-search"></i></button>
            </div>
            <!-- End Search -->
        </form>
        @if(request()->get('search'))
        <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{translate('messages.reset')}}</button>
        @endif
        <div class="search--button-wrapper justify-content-end gap-20px">
            <h5 class="card-title mb-0">{{translate('messages.booking_list')}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$bookings->total()}}</span></h5>
        </div>
    </div>
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
                    <th class="border-0">{{translate('messages.reference')}}</th>
                    <th class="border-0">{{translate('messages.customer')}}</th>
                    <th class="border-0">{{translate('messages.service')}}</th>
                    <th class="border-0">{{translate('messages.date_time')}}</th>
                    <th class="border-0">{{translate('messages.amount')}}</th>
                    <th class="border-0">{{translate('messages.status')}}</th>
                    <th class="text-center border-0">{{translate('messages.action')}}</th>
                </tr>
            </thead>
            <tbody id="set-rows">
                @foreach($bookings ?? [] as $key=>$booking)
                    <tr>
                        <td>{{$key+(isset($bookings) && method_exists($bookings, 'firstItem') ? $bookings->firstItem() : 0)}}</td>
                        <td><span class="text--title">{{ $booking->booking_reference }}</span></td>
                        <td><span class="text--title">{{ Str::limit(($booking->user->f_name ?? '') . ' ' . ($booking->user->l_name ?? ''), 20) }}</span></td>
                        <td><span class="text--title">{{ Str::limit($booking->service->name ?? 'N/A', 20) }}</span></td>
                        <td><span class="text--title">{{ $booking->booking_date->format('Y-m-d') }} {{ $booking->booking_time }}</span></td>
                        <td><span class="text--title">{{ \App\CentralLogics\Helpers::format_currency($booking->total_amount) }}</span></td>
                        <td>
                            <span class="badge badge-{{ $booking->status == 'confirmed' ? 'success' : ($booking->status == 'pending' ? 'warning' : ($booking->status == 'completed' ? 'info' : 'danger')) }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn--container justify-content-center">
                                <a class="btn action-btn btn--warning btn-outline-warning"
                                        href="{{route('admin.beautybooking.booking.view', $booking->id)}}"
                                        title="{{ translate('messages.details') }}"><i
                                            class="tio-visible-outlined"></i>
                                    </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(isset($bookings) && count($bookings) !== 0)
    <hr>
    @endif
    @if(isset($bookings))
        <div class="page-area">
            {!! $bookings->withQueryString()->links() !!}
        </div>
    @endif
    @if(isset($bookings) && count($bookings) === 0)
    <div class="empty--data">
        <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
        <h5>
            {{translate('no_data_found')}}
        </h5>
    </div>
    @endif
</div>

