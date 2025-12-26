@extends('beautybooking::admin.banner.index')

@section('content')
    @parent
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ translate('messages.push_notifications') }}</h5>
        </div>
        <div class="card-body">
            <p class="mb-0">{{ translate('messages.manage_push_notifications_and_assets_here') }}</p>
        </div>
    </div>
@endsection

