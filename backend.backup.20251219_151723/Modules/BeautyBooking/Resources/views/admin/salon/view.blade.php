@extends('layouts.admin.app')

@section('title',$salon->store->name ?? translate('Salon Details'))

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{asset('public/assets/admin/css/croppie.css')}}" rel="stylesheet">
    @php($tab = request('tab', 'overview'))
    @if($tab == 'conversations')
        <link href="{{asset('Modules/Rental/public/assets/css/admin/provider-conversation.css')}}" rel="stylesheet">
    @endif
@endpush

@section('content')
    <div class="content container-fluid">

        @if(session('beauty_salon_status_message'))
            <div class="alert alert-success mb-3">
                {{ session('beauty_salon_status_message') }}
            </div>
        @endif

        @if(($salon->verification_status ?? 0) == 0)
            <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3" dusk="pending-verification-banner">
                <span>Pending Verification</span>
                <form method="POST" action="{{ route('admin.beautybooking.salon.approve', $salon->id) }}" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary" dusk="approve-button">
                        {{ translate('Approve') }}
                    </button>
                </form>
            </div>
        @endif

        @include('beautybooking::admin.salon.details.partials._header',['salon'=>$salon])

        @php($tab = request('tab', 'overview'))
        
        @if($tab == 'overview')
            @include('beautybooking::admin.salon.details.overview')
        @elseif($tab == 'bookings')
            @include('beautybooking::admin.salon.details.bookings')
        @elseif($tab == 'staff')
            @include('beautybooking::admin.salon.details.staff')
        @elseif($tab == 'services')
            @include('beautybooking::admin.salon.details.services')
        @elseif($tab == 'reviews')
            @include('beautybooking::admin.salon.details.reviews')
        @elseif($tab == 'transactions')
            @include('beautybooking::admin.salon.details.transactions')
        @elseif($tab == 'documents')
            @include('beautybooking::admin.salon.details.documents')
        @elseif($tab == 'settings')
            @include('beautybooking::admin.salon.details.settings')
        @elseif($tab == 'disbursements')
            @include('beautybooking::admin.salon.details.disbursement')
        @elseif($tab == 'conversations')
            @include('beautybooking::admin.salon.details.conversations')
        @endif

    </div>
@endsection

@push('script_2')
    @php($tab = request('tab', 'overview'))
    @if($tab == 'conversations')
        <script src="{{asset('Modules/Rental/public/assets/js/admin/view-pages/provi-conversation.js')}}"></script>
    @endif
@endpush

