@extends('layouts.admin.app')

@section('title', translate('messages.stores'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/zone.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.stores') }}</span>
                    </h1>
                </div>
                <div>
                    <a class="btn btn--primary" href="{{ route('admin.beautybooking.store.create-redirect') }}">
                        <i class="tio-add"></i> {{ translate('messages.add_new_store') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title text--title">
                        {{ translate('messages.store_list') }}
                        <span class="badge badge-soft-dark ml-2 rounded-circle" id="itemCount">{{ $stores->total() }}</span>
                    </h5>
                    <form class="search-form flex-grow-1 max-w-353px">
                        <div class="input-group input--group">
                            <input type="search" name="search" value="{{ request()->search }}"
                                   class="form-control" placeholder="{{ translate('messages.search_store') }}">
                            <button type="submit" class="btn btn--secondary bg--primary"><i class="tio-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ translate('sl') }}</th>
                        <th>{{ translate('messages.name') }}</th>
                        <th>{{ translate('messages.vendor') }}</th>
                        <th>{{ translate('messages.zone') }}</th>
                        <th>{{ translate('messages.status') }}</th>
                        <th class="text-center">{{ translate('messages.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($stores as $k => $store)
                        <tr>
                            <td>{{ $k + $stores->firstItem() }}</td>
                            <td>{{ $store->name }}</td>
                            <td>{{ $store->vendor?->f_name }} {{ $store->vendor?->l_name }}</td>
                            <td>{{ $store->zone?->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $store->status ? 'success' : 'secondary' }}">
                                    {{ $store->status ? translate('messages.active') : translate('messages.inactive') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a class="btn btn-sm btn--primary"
                                   href="{{ route('admin.beautybooking.store.edit-redirect', $store->id) }}">
                                    <i class="tio-edit"></i> {{ translate('messages.edit') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if ($stores->isEmpty())
                <div class="empty--data">
                    <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                    <h5>{{ translate('no_data_found') }}</h5>
                </div>
            @else
                <div class="page-area">
                    {!! $stores->appends(request()->query())->links() !!}
                </div>
            @endif
        </div>
    </div>
@endsection

