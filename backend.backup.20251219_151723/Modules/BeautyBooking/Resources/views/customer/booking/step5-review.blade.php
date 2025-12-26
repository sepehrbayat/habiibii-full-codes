<div class="card mb-3">
    <div class="card-body">
        <h6>{{ translate('Booking Details') }}</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>{{ translate('Salon') }}:</strong></td>
                <td class="text-right" id="review-salon">{{ translate('Loading...') }}</td>
            </tr>
            <tr>
                <td><strong>{{ translate('Service') }}:</strong></td>
                <td class="text-right" id="review-service">{{ translate('Loading...') }}</td>
            </tr>
            @if(isset($sessionData['staff_id']) && $sessionData['staff_id'])
            <tr>
                <td><strong>{{ translate('Staff') }}:</strong></td>
                <td class="text-right" id="review-staff">{{ translate('Loading...') }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>{{ translate('Date') }}:</strong></td>
                <td class="text-right" id="review-date">{{ translate('Loading...') }}</td>
            </tr>
            <tr>
                <td><strong>{{ translate('Time') }}:</strong></td>
                <td class="text-right" id="review-time">{{ translate('Loading...') }}</td>
            </tr>
            <tr>
                <td><strong>{{ translate('Payment Method') }}:</strong></td>
                <td class="text-right" id="review-payment">{{ translate('Loading...') }}</td>
            </tr>
            <tr class="table-primary">
                <td><strong>{{ translate('Total Amount') }}:</strong></td>
                <td class="text-right"><strong id="review-total">{{ translate('Loading...') }}</strong></td>
            </tr>
        </table>
    </div>
</div>

<div class="alert alert-info">
    <i class="tio-info"></i> {{ translate('Please review your booking details before confirming.') }}
</div>

