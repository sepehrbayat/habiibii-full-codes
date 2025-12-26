@extends('layouts.admin.app')

@section('title', translate('messages.banners'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('public/assets/admin/img/zone.png') }}" class="w--22" alt="">
                </span>
                <span>{{ translate('messages.banners') }}</span>
            </h1>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="mb-0">{{ translate('messages.select_a_banner_category_from_sidebar') }}</p>
            </div>
        </div>
    </div>
@endsection

