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
    <title>{{ $status === 'approved' ? translate('Salon_Approval_Notification') : translate('Salon_Rejection_Notification') }}</title>
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
        .status-approved {
            color: #28a745;
            font-weight: bold;
        }
        .status-rejected {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body style="background-color: #e9ecef;padding:15px">
    <table dir="{{ $site_direction }}" class="main-table">
        <tbody>
            <tr>
                <td>
                    <h2>{{ $status === 'approved' ? translate('Salon_Approval_Notification') : translate('Salon_Rejection_Notification') }}</h2>
                    <div class="mb-2">
                        {{ translate('Dear_Vendor') }},
                    </div>
                    <div class="mb-2">
                        @if($status === 'approved')
                            <p class="status-approved">{{ translate('Congratulations_Your_salon_has_been_approved') }}: <strong>{{ $store_name }}</strong></p>
                            <p>{{ translate('You_can_now_start_managing_your_salon_and_accepting_bookings') }}.</p>
                        @else
                            <p class="status-rejected">{{ translate('Your_salon_registration_has_been_rejected') }}: <strong>{{ $store_name }}</strong></p>
                            @if($notes)
                                <p><strong>{{ translate('Reason') }}:</strong> {{ $notes }}</p>
                            @endif
                        @endif
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

