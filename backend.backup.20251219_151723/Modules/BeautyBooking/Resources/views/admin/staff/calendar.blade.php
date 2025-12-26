@extends('layouts.admin.app')

@section('title', translate('messages.staff_calendar'))

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
                            <img src="{{ asset('public/assets/admin/img/zone.png') }}" class="w--22" alt="">
                        </span>
                        <span>{{ translate('messages.staff_calendar') }}</span>
                    </h1>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select id="salon-filter" class="form-control">
                        <option value="">{{ translate('messages.All_Salons') }}</option>
                        @foreach($salons as $salon)
                            <option value="{{ $salon->id }}">{{ $salon->store->name ?? ('Salon #' . $salon->id) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <div class="card-body">
                <div id="admin-staff-calendar"
                     data-events-url="{{ $eventsUrl }}"
                     data-booking-view-url="{{ url('admin/beautybooking/booking/view') }}/">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";

        (function() {
            const FC_SRC = "https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js";

            function initStaffCalendar() {
                const calendarEl = document.getElementById('admin-staff-calendar');
                if (!calendarEl || !window.FullCalendar || !window.FullCalendar.Calendar) {
                    return;
                }
                const eventsUrl = calendarEl.dataset.eventsUrl;
                const bookingViewBase = calendarEl.dataset.bookingViewUrl;
                const salonFilter = document.getElementById('salon-filter');

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: fetchEvents,
                    eventClick: function(info) {
                        if (info.event.url) {
                            window.location.href = info.event.url;
                            info.jsEvent.preventDefault();
                        }
                    },
                    eventMouseEnter: function(info) {
                        const props = info.event.extendedProps || {};
                        const tooltip = `
                        <div class="calendar-tooltip">
                            <strong>${info.event.title}</strong><br>
                            ${props.booking_reference || ''}<br>
                            ${props.customer_name || ''}<br>
                            ${props.salon_name || ''}<br>
                            {{ translate('messages.status') }}: ${props.status || ''}
                        </div>
                    `;
                        $(info.el).tooltip({
                            title: tooltip,
                            html: true,
                            placement: 'top'
                        });
                    },
                });

                calendar.render();

                salonFilter.addEventListener('change', function() {
                    calendar.refetchEvents();
                });

                function fetchEvents(fetchInfo, successCallback, failureCallback) {
                    const params = new URLSearchParams({
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr,
                    });

                    if (salonFilter.value) {
                        params.append('salon_id', salonFilter.value);
                    }

                    fetch(eventsUrl + '?' + params.toString(), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                    })
                        .then(response => response.json())
                        .then(data => {
                            const events = (data || []).map(event => ({
                                ...event,
                                url: bookingViewBase ? bookingViewBase + event.id : event.url
                            }));
                            successCallback(events);
                        })
                        .catch(error => {
                            console.error('Failed to load calendar events', error);
                            failureCallback(error);
                        });
                }
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
                document.addEventListener('DOMContentLoaded', () => ensureFullCalendar(initStaffCalendar));
            } else {
                ensureFullCalendar(initStaffCalendar);
            }
        })();
    </script>
@endpush


