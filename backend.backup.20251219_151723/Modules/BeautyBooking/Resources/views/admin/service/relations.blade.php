@extends('layouts.admin.app')

@section('title', translate('messages.service_relations'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/category.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.service_relations') }}</span>
                    </h1>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header py-2">
                <h5 class="card-title mb-0">{{ translate('messages.add_relation') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.beautybooking.service-relations.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <label class="input-label">{{ translate('messages.service') }}</label>
                            <select name="service_id" class="form-control js-select2-custom" required>
                                <option value="">{{ translate('messages.select') }}</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }} (ID: {{ $service->id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="input-label">{{ translate('messages.related_service') }}</label>
                            <select name="related_service_id" class="form-control js-select2-custom" required>
                                <option value="">{{ translate('messages.select') }}</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }} (ID: {{ $service->id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="input-label">{{ translate('messages.relation_type') }}</label>
                            <select name="relation_type" class="form-control" required>
                                <option value="complementary">{{ translate('messages.complementary') }}</option>
                                <option value="upsell">{{ translate('messages.upsell') }}</option>
                                <option value="cross_sell">{{ translate('messages.cross_sell') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="input-label">{{ translate('messages.priority') }}</label>
                            <input type="number" name="priority" class="form-control" value="0" min="0" max="100">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn--primary w-100">{{ translate('messages.save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-2">
                <h5 class="card-title mb-0">{{ translate('messages.relations') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                        <tr>
                            <th>{{ translate('messages.service') }}</th>
                            <th>{{ translate('messages.related_service') }}</th>
                            <th>{{ translate('messages.relation_type') }}</th>
                            <th>{{ translate('messages.priority') }}</th>
                            <th>{{ translate('messages.status') }}</th>
                            <th class="text-center">{{ translate('messages.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($relations as $relation)
                            <tr>
                                <td>{{ $relation->service?->name ?? '-' }} ({{ $relation->service_id }})</td>
                                <td>{{ $relation->relatedService?->name ?? '-' }} ({{ $relation->related_service_id }})</td>
                                <td>{{ ucfirst(str_replace('_',' ', $relation->relation_type)) }}</td>
                                <td>{{ $relation->priority }}</td>
                                <td>
                                    <span class="badge badge-{{ $relation->status ? 'success' : 'secondary' }}">
                                        {{ $relation->status ? translate('messages.active') : translate('messages.inactive') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.beautybooking.service-relations.delete', $relation->id) }}" method="POST" onsubmit="return confirm('{{ translate('messages.Are_you_sure?') }}');">
                                        @csrf
                    @method('delete')
                                        <button type="submit" class="btn btn-sm btn--danger btn-outline-danger">
                                            <i class="tio-delete-outlined"></i> {{ translate('messages.delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="page-area">
                    {!! $relations->withQueryString()->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

