@extends('layouts.admin.app')

@section('title', translate('messages.Invoice'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('Modules/BeautyBooking/public/assets/css/admin/booking-invoice.css') }}" media="print">
@endpush


@section('content')

@include('beautybooking::admin.booking.partials._invoice')

@endsection

