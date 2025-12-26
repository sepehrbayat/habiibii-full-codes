<!-- Reviews Tab -->
<div class="card">
    <div class="card-header py-2">
        <div class="search--button-wrapper">
            <h5 class="card-title">{{translate('messages.reviews')}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$reviews->total()}}</span></h5>
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
                    <th class="border-0">{{translate('messages.customer')}}</th>
                    <th class="border-0">{{translate('messages.rating')}}</th>
                    <th class="border-0">{{translate('messages.comment')}}</th>
                    <th class="border-0">{{translate('messages.status')}}</th>
                    <th class="text-center border-0">{{translate('messages.action')}}</th>
                </tr>
            </thead>
            <tbody id="set-rows">
                @foreach($reviews ?? [] as $key=>$review)
                    <tr>
                        <td>{{$key+(isset($reviews) && method_exists($reviews, 'firstItem') ? $reviews->firstItem() : 0)}}</td>
                        <td><span class="text--title">{{ Str::limit(($review->user->f_name ?? '') . ' ' . ($review->user->l_name ?? ''), 20) }}</span></td>
                        <td>
                            @for($i = 1; $i <= 5; $i++)
                                <i class="tio-star{{ $i <= $review->rating ? '' : '-outlined' }} text-warning"></i>
                            @endfor
                        </td>
                        <td><span class="text--title">{{ Str::limit($review->comment ?? 'N/A', 50) }}</span></td>
                        <td>
                            <span class="badge badge-{{ $review->status == 'approved' ? 'success' : ($review->status == 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($review->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn--container justify-content-center">
                                @if($review->status == 'pending')
                                    <form action="{{ route('admin.beautybooking.review.approve', $review->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn action-btn btn--success btn-outline-success" title="{{ translate('messages.approve') }}">
                                            <i class="tio-checkmark"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.beautybooking.review.reject', $review->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn action-btn btn--danger btn-outline-danger" title="{{ translate('messages.reject') }}">
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
    @if(isset($reviews) && count($reviews) !== 0)
    <hr>
    @endif
    @if(isset($reviews))
        <div class="page-area">
            {!! $reviews->withQueryString()->links() !!}
        </div>
    @endif
    @if(isset($reviews) && count($reviews) === 0)
    <div class="empty--data">
        <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
        <h5>
            {{translate('no_data_found')}}
        </h5>
    </div>
    @endif
</div>

