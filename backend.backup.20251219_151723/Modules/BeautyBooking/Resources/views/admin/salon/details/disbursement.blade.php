<!-- Disbursements Tab Content -->
<!-- محتوای تب پرداخت‌ها -->
<div class="card">
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title">
                        {{ translate('messages.Total_Disbursements') }} <span class="badge badge-soft-secondary ml-2" id="countItems">{{ $disbursements->total() }}</span>
                    </h5>
                    <form class="search-form">
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input class="form-control" value="{{ request()?->search  ?? null }}" placeholder="{{ translate('messages.search_by_salon_info') }}" name="search">
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>
                    <!-- Static Export Button -->
                    @if(method_exists($salon, 'store') && $salon->store)
                    <div class="hs-unfold ml-3">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm" href="javascript:;"
                           data-hs-unfold-options='{
                        "target": "#usersExportDropdown",
                        "type": "css-animation"
                    }'>
                            <i class="tio-download-to mr-1"></i> {{translate('messages.export')}}
                        </a>
                        <div id="usersExportDropdown"
                             class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{translate('messages.download_options')}}</span>
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.store.disbursement-export', ['provider_id'=>request()->id,'id'=>$salon->store->id,'type'=>'excel'])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('public/assets/admin')}}/svg/components/excel.svg" alt="Image Description">
                                {{translate('messages.excel')}}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.store.disbursement-export', ['provider_id'=>request()->id,'id'=>$salon->store->id,'type'=>'csv'])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('public/assets/admin')}}/svg/components/placeholder-csv-format.svg" alt="Image Description">
                                {{translate('messages.csv')}}
                            </a>
                        </div>
                    </div>
                    @endif
                    <!-- Static Export Button -->

                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-thead-bordered table-align-middle card-table">
                        <thead>
                        <tr>
                            <th>{{ translate('messages.sl') }}</th>
                            <th>{{ translate('messages.id') }}</th>
                            <th>{{ translate('messages.Disburse_Amount') }}</th>
                            <th>{{ translate('messages.Payment_method') }}</th>
                            <th>{{ translate('messages.status') }}</th>
                            <th>
                                <div class="text-center">
                                    {{ translate('messages.action') }}
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($disbursements as $key => $disbursement)
                            <tr>
                                <td>
                                    <span class="font-weight-bold">{{$key+$disbursements->firstItem()  }}</span>
                                </td>
                                <td>
                                    #{{ $disbursement->disbursement_id }}
                                </td>
                                <td>
                                    {{\App\CentralLogics\Helpers::format_currency($disbursement['disbursement_amount'])}}
                                </td>
                                <td>
                                    <div>
                                        {{$disbursement->withdraw_method->method_name ?? translate('messages.N/A')}}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-soft-primary">{{$disbursement->status}}</span>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn" data-toggle="modal" data-target="#payment-info-{{$disbursement->id}}" title="{{ translate('messages.view_details') }}">
                                            <i class="tio-visible"></i>
                                        </a>
                                    </div>
                                </td>
                                <div class="modal fade" id="payment-info-{{$disbursement->id}}">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header pb-4">
                                                <button type="button" class="payment-modal-close btn-close border-0 outline-0 bg-transparent" data-dismiss="modal">
                                                    <i class="tio-clear"></i>
                                                </button>
                                                <div class="w-100 text-center">
                                                    <h2 class="mb-2">{{ translate('messages.Payment_Information') }}</h2>
                                                    <div>
                                                        <span class="mr-2">{{ translate('messages.Disbursement_ID') }}</span>
                                                        <strong>#{{$disbursement->disbursement_id}}</strong>
                                                    </div>
                                                    <div class="mt-2">
                                                        <span class="mr-2">{{ translate('messages.status') }}</span>
                                                        <span class="badge badge-soft-primary">{{$disbursement->status}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-body">
                                                <div class="card shadow--card-2">
                                                    <div class="card-body">
                                                        <div class="d-flex flex-wrap payment-info-modal-info p-xl-4">
                                                            <div class="item">
                                                                <h5>{{ translate('messages.Salon_Information') }}</h5>
                                                                <ul class="item-list">
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('messages.name') }}</span>
                                                                        <span>:</span>
                                                                        <strong>{{$disbursement?->store?->name ?? translate('messages.N/A')}}</strong>
                                                                    </li>
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('messages.contact') }}</span>
                                                                        <span>:</span>
                                                                        <strong>{{$disbursement?->store?->phone ?? translate('messages.N/A')}}</strong>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="item">
                                                                <h5>{{ translate('messages.Owner_Information') }}</h5>
                                                                <ul class="item-list">
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('messages.name') }}</span>
                                                                        <span>:</span>
                                                                        <strong>{{$disbursement->store->vendor->f_name ?? ''}} {{$disbursement->store->vendor->l_name ?? ''}}</strong>
                                                                    </li>
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('messages.email') }}</span>
                                                                        <span>:</span>
                                                                        <strong>{{$disbursement->store->vendor->email ?? translate('messages.N/A')}}</strong>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="item w-100">
                                                                <h5>{{ translate('messages.Account_Information') }}</h5>
                                                                <ul class="item-list">
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('messages.payment_method') }}</span><strong>{{$disbursement->withdraw_method->method_name ?? translate('messages.N/A')}}</strong>
                                                                    </li>
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ translate('messages.amount') }}</span>
                                                                        <strong>{{\App\CentralLogics\Helpers::format_currency($disbursement['disbursement_amount'])}}</strong>
                                                                    </li>
                                                                    @forelse($disbursement->withdraw_method ? json_decode($disbursement->withdraw_method->method_fields, true) ?? [] : [] as $key=> $item)
                                                                        <li class="d-flex flex-wrap">
                                                                            <span class="name">{{  translate($key) }}</span>
                                                                            <strong>{{$item}}</strong>
                                                                        </li>
                                                                    @empty

                                                                    @endforelse

                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

            @if(count($disbursements) === 0)

                        <div class="empty--data">
                            <img src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                            <h5>
                                {{translate('messages.no_data_found')}}
                            </h5>
                        </div>
                    @endif
                </div>
            </div>
            <div class="page-area px-4 pb-3">
                <div class="d-flex align-items-center justify-content-end">
                    <div>
                        {!!$disbursements->links()!!}
                    </div>
                </div>
    </div>
</div>