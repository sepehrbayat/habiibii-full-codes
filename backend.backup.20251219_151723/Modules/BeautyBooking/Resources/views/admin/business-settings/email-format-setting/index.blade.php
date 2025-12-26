@extends('layouts.admin.app')

@section('title', translate('email_template'))
@push('css_or_js')
<link rel="stylesheet" href="{{asset('public/assets/admin/css/view-pages/email-templates.css')}}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center __gap-15px">
                <h1 class="page-header-title mr-3 mb-0">
                    <span class="page-header-icon">
                        <img src="{{ asset('public/assets/admin/img/email-setting.png') }}" class="w--26" alt="">
                    </span>
                    <span>
                        {{ translate('messages.Email Templates') }}
                    </span>
                </h1>
            </div>
            <!-- Nav Scroller -->
            <div class="js-nav-scroller hs-nav-scroller-horizontal mt-3">
                <ul class="nav nav-tabs border-0 nav--tabs">
                    <li class="nav-item">
                        <a class="nav-link {{request('type') == 'admin' || !request('type') ? 'active' : ''}}" 
                           href="{{ route('admin.beautybooking.email-format-setting', ['type' => 'admin']) }}">
                            {{ translate('messages.Admin_Email_Formats') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{request('type') == 'vendor' ? 'active' : ''}}" 
                           href="{{ route('admin.beautybooking.email-format-setting', ['type' => 'vendor']) }}">
                            {{ translate('messages.Vendor_Email_Formats') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{request('type') == 'customer' ? 'active' : ''}}" 
                           href="{{ route('admin.beautybooking.email-format-setting', ['type' => 'customer']) }}">
                            {{ translate('messages.Customer_Email_Formats') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="tio-info-outlined"></i>
                    {{ translate('messages.Email format settings for Beauty Booking module. Configure email templates for salon registration, booking confirmations, and notifications.') }}
                </div>
                <p class="text-muted">
                    {{ translate('messages.This feature allows you to customize email templates sent to admins, vendors, and customers for various Beauty Booking events.') }}
                </p>
            </div>
        </div>
    </div>
@endsection

