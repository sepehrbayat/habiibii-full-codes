@extends('layouts.admin.app')

@section('title', translate('messages.subscription_plans'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/zone.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.subscription_plans') }}</span>
                    </h1>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-2">
                <h5 class="card-title mb-0">{{ translate('messages.available_plans_for_beauty') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                        <tr>
                            <th>{{ translate('messages.plan') }}</th>
                            <th>{{ translate('messages.price') }}</th>
                            <th>{{ translate('messages.validity_days') }}</th>
                            <th>{{ translate('messages.max_order') }}</th>
                            <th>{{ translate('messages.max_product') }}</th>
                            <th>{{ translate('messages.features') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($plans as $plan)
                            <tr>
                                <td>
                                    <span class="text--title">{{ $plan->package_name }}</span>
                                    @if($plan->default)
                                        <span class="badge badge-soft-info ml-1">{{ translate('messages.default') }}</span>
                                    @endif
                                </td>
                                <td>{{ \App\CentralLogics\Helpers::format_currency($plan->price) }}</td>
                                <td>{{ $plan->validity }}</td>
                                <td>{{ $plan->max_order ?? translate('messages.unlimited') }}</td>
                                <td>{{ $plan->max_product ?? translate('messages.unlimited') }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if($plan->pos)<span class="badge badge-soft-success">POS</span>@endif
                                        @if($plan->mobile_app)<span class="badge badge-soft-success">App</span>@endif
                                        @if($plan->chat)<span class="badge badge-soft-success">Chat</span>@endif
                                        @if($plan->review)<span class="badge badge-soft-success">Review</span>@endif
                                        @if($plan->self_delivery)<span class="badge badge-soft-success">Self Delivery</span>@endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if($plans->isEmpty())
                    <div class="empty--data">
                        <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                        <h5>{{ translate('no_data_found') }}</h5>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

