@extends('layouts.admin.app')

@section('title', translate('Booking Calendar'))

@push('css_or_js')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="page-header-title text-break">
                        <span class="page-header-icon">
                            <img src="{{ asset('public/assets/admin/img/beauty/calendar.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{translate('messages.Booking_Calendar')}}
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-body">
                <div id="booking-calendar"></div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    "use strict";

    (function() {
        const FC_SRC = "https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js";

        function renderBookingCalendar() {
            const calendarEl = document.getElementById('booking-calendar');
            if (!calendarEl || !window.FullCalendar || !window.FullCalendar.Calendar) {
                return;
            }
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: @json($bookings),
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        return false;
                    }
                }
            });
            calendar.render();
        }

        function ensureFullCalendar(cb) {
            if (window.FullCalendar && window.FullCalendar.Calendar) {
                cb();
                return;
            }
            const script = document.createElement('script');
            script.src = FC_SRC;
            script.onload = cb;
            document.head.appendChild(script);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => ensureFullCalendar(renderBookingCalendar));
        } else {
            ensureFullCalendar(renderBookingCalendar);
        }
    })();
</script>
@endpush

