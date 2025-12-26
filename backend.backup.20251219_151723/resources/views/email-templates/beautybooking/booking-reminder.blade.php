<!DOCTYPE html>
<?php
    $lang = \App\CentralLogics\Helpers::system_default_language();
    $site_direction = \App\CentralLogics\Helpers::system_default_direction();
?>
<html lang="{{ $lang }}" class="{{ $site_direction === 'rtl'?'active':'' }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('Booking_Reminder') }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;1,400&display=swap');
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
            line-height: 21px;
            color: #737883;
            background: #f7fbff;
            padding: 0;
            display: flex;align-items: center;justify-content: center;
            min-height: 100vh;
        }
        h1,h2,h3,h4,h5,h6 {
            color: #334257;
        }
        * {
            box-sizing: border-box
        }
        :root {
           --base: #006161
        }
        .main-table {
            width: 500px;
            background: #FFFFFF;
            margin: 0 auto;
            padding: 40px;
        }
        img {
            max-width: 100%;
        }
        .cmn-btn{
            background: var(--base);
            color: #fff;
            padding: 8px 20px;
            display: inline-block;
            text-decoration: none;
        }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mb-3 { margin-bottom: 15px; }
        .mb-4 { margin-bottom: 20px; }
        hr {
            border-color : rgba(0, 170, 109, 0.3);
            margin: 16px 0
        }
        .booking-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .booking-details p {
            margin: 5px 0;
        }
        .reminder-note {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body style="background-color: #e9ecef;padding:15px">
    <table dir="{{ $site_direction }}" class="main-table">
        <tbody>
            <tr>
                <td>
                    <h2>{{ translate('Booking_Reminder') }}</h2>
                    <div class="mb-2">
                        {{ translate('Dear') }} {{ $user_name }},
                    </div>
                    <div class="reminder-note">
                        <p><strong>{{ translate('Reminder') }}:</strong> {{ translate('You_have_a_booking_tomorrow') }}.</p>
                    </div>
                    <div class="booking-details">
                        <p><strong>{{ translate('Booking_Reference') }}:</strong> {{ $booking->booking_reference }}</p>
                        <p><strong>{{ translate('Salon') }}:</strong> {{ $salon_name }}</p>
                        <p><strong>{{ translate('Service') }}:</strong> {{ $booking->service->name ?? '' }}</p>
                        <p><strong>{{ translate('Date') }}:</strong> {{ $booking->booking_date->format('Y-m-d') }}</p>
                        <p><strong>{{ translate('Time') }}:</strong> {{ $booking->booking_time }}</p>
                    </div>
                    <div class="mb-2">
                        {{ translate('Please_arrive_on_time_for_your_appointment') }}.
                    </div>
                    <hr>
                    <div class="mb-2">
                        {{ translate('Thanks_&_Regards') }},
                    </div>
                    <div class="mb-4">
                        {{ $company_name }}
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>

