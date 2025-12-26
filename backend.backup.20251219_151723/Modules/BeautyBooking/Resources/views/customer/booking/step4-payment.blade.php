<div class="card mb-3">
    <div class="card-body">
        <h6>{{ translate('Booking Summary') }}</h6>
        <table class="table table-sm">
            <tr>
                <td>{{ translate('Service') }}:</td>
                <td class="text-right"><strong id="summary-service">{{ $service->name ?? translate('N/A') }}</strong></td>
            </tr>
            <tr>
                <td>{{ translate('Duration') }}:</td>
                <td class="text-right" id="summary-duration">{{ ($service->duration_minutes ?? 60) }} {{ translate('minutes') }}</td>
            </tr>
            <tr>
                <td>{{ translate('Date & Time') }}:</td>
                <td class="text-right" id="summary-datetime">
                    @if(isset($bookingDate) && isset($bookingTime))
                        @php
                            try {
                                $date = \Carbon\Carbon::parse($bookingDate);
                                // Parse time - could be "H:i" format or full datetime
                                // تجزیه زمان - می‌تواند فرمت "H:i" یا datetime کامل باشد
                                if (strlen($bookingTime) <= 5) {
                                    // Time only (e.g., "14:00")
                                    // فقط زمان (مثلاً "14:00")
                                    $timeParts = explode(':', $bookingTime);
                                    $date->setTime((int)($timeParts[0] ?? 0), (int)($timeParts[1] ?? 0));
                                } else {
                                    // Full datetime
                                    // datetime کامل
                                    $date = \Carbon\Carbon::parse($bookingTime);
                                }
                                $formattedDateTime = $date->format('M d, Y') . ' at ' . $date->format('H:i');
                            } catch (\Exception $e) {
                                $formattedDateTime = $bookingDate . ' ' . $bookingTime;
                            }
                        @endphp
                        {{ $formattedDateTime }}
                    @else
                        {{ translate('Not selected') }}
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>{{ translate('Subtotal') }}:</strong></td>
                <td class="text-right"><strong id="summary-subtotal">{{ number_format(($amounts['base_price'] ?? 0) + ($amounts['additional_services_amount'] ?? 0), 2) }}</strong></td>
            </tr>
            @if(($amounts['service_fee'] ?? 0) > 0)
            <tr>
                <td>{{ translate('Service Fee') }}:</td>
                <td class="text-right" id="summary-service-fee">{{ number_format($amounts['service_fee'] ?? 0, 2) }}</td>
            </tr>
            @endif
            @if(($amounts['tax_amount'] ?? 0) > 0)
            <tr>
                <td>{{ translate('Tax') }}:</td>
                <td class="text-right">{{ number_format($amounts['tax_amount'] ?? 0, 2) }}</td>
            </tr>
            @endif
            @if(($amounts['discount'] ?? 0) > 0)
            <tr>
                <td>{{ translate('Discount') }}:</td>
                <td class="text-right text-success">-{{ number_format($amounts['discount'] ?? 0, 2) }}</td>
            </tr>
            @endif
            @if(($amounts['consultation_credit'] ?? 0) > 0)
            <tr>
                <td>{{ translate('Consultation Credit') }}:</td>
                <td class="text-right text-success">-{{ number_format($amounts['consultation_credit'] ?? 0, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>{{ translate('Total') }}:</strong></td>
                <td class="text-right"><strong class="text-primary" id="summary-total">{{ number_format($amounts['total'] ?? 0, 2) }}</strong></td>
            </tr>
        </table>
    </div>
</div>

@if(isset($suggestions) && count($suggestions) > 0)
<div class="card mb-3">
    <div class="card-body">
        <h6>{{ translate('Recommended Add-ons') }}</h6>
        <div class="list-group">
            @foreach($suggestions as $suggestion)
                <label class="list-group-item">
                    <input type="checkbox" name="addon_services[]" value="{{ $suggestion->id }}">
                    <div class="ms-3">
                        <strong>{{ $suggestion->name }}</strong>
                        <div class="small text-muted">
                            {{ number_format($suggestion->price, 2) }} • {{ $suggestion->duration_minutes }} {{ translate('minutes') }}
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
    </div>
</div>
@endif

