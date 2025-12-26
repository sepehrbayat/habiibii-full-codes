<div class="content container-fluid invoice-page initial-38">
    <div id="printableArea">
        <div>
            <div class="text-center">
                <input type="button" class="btn btn-primary mt-3 non-printable print-Div"
                       value="{{ translate('Proceed,_If_thermal_printer_is_ready.') }}" />
                <a href="{{ url()->previous() }}"
                   class="btn btn-danger non-printable mt-3">{{ translate('messages.back') }}</a>
            </div>

            <hr class="non-printable">

            <div class="print--invoice initial-38-1">
                @if ($booking?->salon?->store)
                    <div class="text-center pt-4 mb-3">
                        <img class="invoice-logo" src="{{ asset('/public/assets/admin/img/car_icon.svg') }}"
                             alt="">
                        <div class="top-info">
                            <h2 class="store-name">
                                 {{ $booking?->salon?->store?->name }}
                            </h2>
                            <div>
                                <img src="{{ asset('/public/assets/admin/img/location_icon.svg') }}" alt="">
                                {{ $booking?->salon?->store?->address ?? 'N/A' }}
                            </div>
                            <div class="mt-1 d-flex justify-content-center">
                                <span><img src="{{ asset('/public/assets/admin/img/phone_icon.svg') }}" alt=""></span>&nbsp;
                                <span>{{ $booking?->salon?->store?->phone ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="img-wrap">
                    <div class="top-info">

                        <img src="{{ asset('/public/assets/admin/img/line_icon.svg') }}" alt="" class="w-100">
                    </div>
                    <div class="order-info-id text-center">
                        <div class="d-flex justify-content-center mb-2 fs-12">
                            <span class="fw-medium">{{ translate('messages.Booking_ID') }}</span>
                            <span>:</span>
                            <span class="fw-medium">{{ $booking?->booking_reference }}</span>
                        </div>
                        <div>
                            {{ \App\CentralLogics\Helpers::time_date_format($booking?->booking_date) }} {{ $booking?->booking_time }}
                        </div>
                        @if ($booking->salon->store?->gst_status)
                            <div>
                                <span>{{ translate('Gst No') }}</span> <span>:</span> <span>{{ $booking?->salon?->store?->gst_code ?? 'N/A' }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="order-info-details">
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="mb-1">
                                    <span class="opacity-70">{{ translate('messages.customer_name') }}</span> <span>:</span>
                                    <span>{{ ($booking?->user->f_name ?? '') . ' ' . ($booking?->user->l_name ?? '') }}</span>
                                </div>
                                <div class="mb-1">
                                    <span class="opacity-70">{{ translate('messages.phone') }}</span> <span>:</span>
                                    <span>{{ $booking?->user->phone ?? 'N/A' }}</span>
                                </div>
                                <div class="text-break mb-1">
                                    <span class="opacity-70">{{ translate('messages.Salon') }}</span> <span>:</span>
                                    <span>{{ $booking?->salon?->store?->name ?? 'N/A' }}</span>
                                </div>
                                <div class="text-break mb-1">
                                    <span class="opacity-70">{{ translate('messages.Service') }}</span> <span>:</span>
                                    <span>{{ $booking?->service?->name ?? 'N/A' }}</span>
                                </div>
                                @if ($booking->staff)
                                <div class="text-break mb-1">
                                    <span class="opacity-70">{{ translate('messages.Staff') }}</span> <span>:</span>
                                    <span>{{ $booking?->staff?->name ?? 'N/A' }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div><img src="{{ asset('/public/assets/admin/img/line_icon.svg') }}" alt="" class="w-100"></div>

                        <div>
                            <table class="table invoice--table text-black mb-1">
                                <thead class="border-0">
                                <tr class="border-0">
                                    <th>{{ translate('messages.Service_Details') }}</th>
                                    <th class="w-10p"></th>
                                    <th>{{ translate('messages.price') }}</th>
                                </tr>
                                </thead>

                                <tbody>
                                @php($sub_total = $booking->base_price ?? $booking->service->price ?? 0)
                                <tr>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <div>1.</div>
                                            <div class="opacity-70">
                                                <strong class="d-block mb-1">{{ $booking?->service?->name ?? 'N/A' }}</strong>
                                                <span class="fs-9">
                                                    {{ translate('messages.Category') }}: {{ $booking?->service?->category?->name ?? 'N/A' }},
                                                    {{ translate('messages.Duration') }}: {{ $booking?->service?->duration_minutes ?? 0 }} {{ translate('messages.minutes') }}
                                                    @if ($booking->staff)
                                                    , {{ translate('messages.Staff') }}: {{ $booking->staff->name }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td></td>
                                    <td>
                                        {{ \App\CentralLogics\Helpers::format_currency($sub_total) }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div><img src="{{ asset('/public/assets/admin/img/line_icon.svg') }}" alt="" class="w-100"></div>

                        <div class="checkout--info">
                            <dl class="row text-right">
                                <dt class="col-6 opacity-70">{{ translate('messages.Service_Price') }}:</dt>
                                <dd class="col-6"> {{ \App\CentralLogics\Helpers::format_currency($booking->base_price ?? $booking->service->price ?? 0) }} </dd>

                                @if ($booking->service_fee > 0)
                                <dt class="col-6 opacity-70">{{ translate('messages.Service_Fee') }}:</dt>
                                <dd class="col-6"> +{{ \App\CentralLogics\Helpers::format_currency($booking->service_fee) }}</dd>
                                @endif

                                @if ($booking->tax_amount > 0)
                                <dt class="col-6 opacity-70">{{ translate('messages.tax') }}:</dt>
                                <dd class="col-6"> +{{ \App\CentralLogics\Helpers::format_currency($booking->tax_amount) }}</dd>
                                @endif

                                @if ($booking->discount_amount > 0)
                                <dt class="col-6 opacity-70">{{ translate('messages.Discount') }}:</dt>
                                <dd class="col-6"> -{{ \App\CentralLogics\Helpers::format_currency($booking->discount_amount) }}</dd>
                                @endif

                                @if ($booking->cancellation_fee > 0)
                                <dt class="col-6 opacity-70 text-danger">{{ translate('messages.Cancellation_Fee') }}:</dt>
                                <dd class="col-6 text-danger"> +{{ \App\CentralLogics\Helpers::format_currency($booking->cancellation_fee) }}</dd>
                                @endif

                                <dt class="col-6 total">{{ translate('messages.total') }}:</dt>
                                <dd class="col-6 total"> {{ \App\CentralLogics\Helpers::format_currency($booking->total_amount) }}</dd>

                                @if ($booking->commission_amount > 0)
                                <dt class="col-6 opacity-70">{{ translate('messages.Commission') }}:</dt>
                                <dd class="col-6"> -{{ \App\CentralLogics\Helpers::format_currency($booking->commission_amount) }}</dd>
                                @endif

                                <dt class="col-6 opacity-70">{{ translate('messages.Salon_Payout') }}:</dt>
                                <dd class="col-6"> {{ \App\CentralLogics\Helpers::format_currency($booking->total_amount - ($booking->commission_amount ?? 0)) }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="top-info">
                        <img src="{{ asset('/public/assets/admin/img/line_icon.svg') }}" alt="" class="w-100">
                        <div>{{ translate('Thank You') }}</div>
                        <img src="{{ asset('/public/assets/admin/img/line_icon.svg') }}" alt="" class="w-100">

                        <div class="copyright">
                            &copy; {{ \App\Models\BusinessSetting::where(['key' => 'business_name'])->first()->value ?? 'Beauty Booking' }}.
                            <span class="d-none d-sm-inline-block">{{ \App\Models\BusinessSetting::where(['key' => 'footer_text'])->first()->value ?? '' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script_2')
    <script src="{{asset('Modules/BeautyBooking/public/assets/js/admin/view-pages/invoice.js')}}"></script>
@endpush

