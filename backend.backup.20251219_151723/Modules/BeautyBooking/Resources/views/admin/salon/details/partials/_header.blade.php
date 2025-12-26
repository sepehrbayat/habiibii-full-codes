    <!-- Page Header -->
    <div class="page-header pb-0">
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/store.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.Salon_Details') }}
                    </h1></span>
                </div>
                @if(!request()->tab)
                    <div class="d-flex align-items-start flex-wrap gap-2">
                        <a href="javascript:" class="btn btn--reset d-flex justify-content-between align-items-center gap-4 lh--1 h--45px">
                            {{ translate('messages.status') }}
                            <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$salon->id}}">
                                <input type="checkbox" data-url="{{route('admin.store.status',[$salon->store->id ?? 0,$salon->store->status ?? 0 ?0:1])}}"
                                       class="toggle-switch-input redirect-url" id="stocksCheckbox{{$salon->id}}" {{($salon->store->status ?? 0)?'checked':''}}>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @if($salon->store && $salon->store->vendor && $salon->store->vendor->status)
        <!-- Nav Scroller -->
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <span class="hs-nav-scroller-arrow-prev d-none">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-left"></i>
            </a>
            </span>

            <span class="hs-nav-scroller-arrow-next d-none">
                <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                    <i class="tio-chevron-right"></i>
                </a>
            </span>

            <!-- Nav -->
            <ul class="nav nav-tabs page-header-tabs mb-2">
                <li class="nav-item">
                    <a class="nav-link {{request('tab')==null?'active':''}}" href="{{route('admin.beautybooking.salon.view', $salon->id)}}">{{translate('messages.overview')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='bookings'?'active':''}}" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'bookings'])}}"  aria-disabled="true">{{translate('messages.Booking_List')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='staff'?'active':''}}" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'staff'])}}"  aria-disabled="true">{{translate('messages.staff_list')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='services'?'active':''}}" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'services'])}}"  aria-disabled="true">{{translate('messages.Services')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='reviews'?'active':''}}" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'reviews'])}}"  aria-disabled="true">{{translate('messages.reviews')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='transactions'?'active':''}}" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'transactions'])}}"  aria-disabled="true">{{translate('messages.transactions')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='documents'?'active':''}}" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'documents'])}}"  aria-disabled="true">{{translate('messages.Documents')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='settings'?'active':''}}" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'settings'])}}"  aria-disabled="true">{{translate('messages.settings')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='conversations'?'active':''}}" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'conversations'])}}"  aria-disabled="true">{{translate('messages.Conversations')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{request('tab')=='disbursements'?'active':''}}" href="{{route('admin.beautybooking.salon.view', ['id'=>$salon->id, 'tab'=> 'disbursements'])}}"  aria-disabled="true">{{translate('messages.disbursements')}}</a>
                </li>
            </ul>
            <!-- End Nav -->
        </div>
        <!-- End Nav Scroller -->
        @endif
    </div>
    <!-- End Page Header -->

