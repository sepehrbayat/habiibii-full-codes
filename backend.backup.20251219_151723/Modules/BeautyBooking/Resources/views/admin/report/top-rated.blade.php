@extends('layouts.admin.app')

@section('title', translate('Top Rated Salons'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{asset('/public/assets/admin/img/report/report.png')}}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.Top_Rated_Salons') }}
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card mb-3">
            <div class="card-header">
                <form method="get">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('messages.Year') }}</label>
                            <select name="year" class="form-control">
                                @for($y = now()->year; $y >= now()->year - 4; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('messages.Month') }}</label>
                            <select name="month" class="form-control">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create($year, $m, 1)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn--primary btn-block text-center">{{ translate('messages.filter') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ translate('messages.Reporting_Period') }}:
                    {{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{ translate('Rank') }}</th>
                                <th class="border-0">{{ translate('Salon Name') }}</th>
                                <th class="border-0 text-right">{{ translate('Rating') }}</th>
                                <th class="border-0 text-right">{{ translate('Total Bookings') }}</th>
                                <th class="border-0 text-right">{{ translate('Total Reviews') }}</th>
                                <th class="border-0">{{ translate('Badges') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salons as $index => $salon)
                                <tr>
                                    <td><span class="text--title">#{{ $index + 1 }}</span></td>
                                    <td><span class="text--title">{{ $salon->store->name ?? '' }}</span></td>
                                    <td class="text-right"><span class="text--title">{{ number_format($salon->avg_rating, 2) }}</span></td>
                                    <td class="text-right"><span class="text--title">{{ $salon->total_bookings }}</span></td>
                                    <td class="text-right"><span class="text--title">{{ $salon->total_reviews }}</span></td>
                                    <td>
                                        @foreach($salon->badges_list as $badge)
                                            <span class="badge badge-soft-primary">{{ ucfirst(str_replace('_', ' ', $badge)) }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="empty--data">
                                            <img src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                                            <h5>{{ translate('No top rated salons found') }}</h5>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

