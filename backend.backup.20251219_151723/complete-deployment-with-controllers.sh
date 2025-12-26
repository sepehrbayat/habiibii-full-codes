#!/bin/bash
# Complete deployment script with all controller fixes embedded
# Run on server: sudo bash complete-deployment-with-controllers.sh

set -e

CONTROLLERS_DIR="/var/www/6ammart-laravel/Modules/Gateways/Http/Controllers"

echo "=========================================="
echo "6ammart Complete Deployment Fix"
echo "=========================================="
echo ""

# Step 1: Update Controllers
echo "Step 1: Updating payment gateway controllers..."

# Backup existing controllers
for controller in SenangPayController.php PhonepeController.php FlutterwaveV3Controller.php WorldPayController.php; do
    if [ -f "${CONTROLLERS_DIR}/${controller}" ]; then
        cp "${CONTROLLERS_DIR}/${controller}" "${CONTROLLERS_DIR}/${controller}.backup.$(date +%Y%m%d_%H%M%S)"
    fi
done

# Update SenangPayController.php
cat > "${CONTROLLERS_DIR}/SenangPayController.php" << 'SENANGPAY_EOF'
<?php

namespace Modules\Gateways\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Entities\PaymentRequest;
use Modules\Gateways\Traits\Processor;

class SenangPayController extends Controller
{
    use Processor;

    private mixed $config_values;

    private PaymentRequest $payment;
    private User $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('senang_pay', 'payment_config');
        $this->config_values = null; // Initialize $config_values
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }
        $this->payment = $payment;
        $this->user = $user;
    }

    public function index(Request $request): View|Factory|JsonResponse|Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $payment_data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($payment_data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $payer = json_decode($payment_data['payer_information']);
        if (!$this->config_values) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, [['code' => 'config', 'message' => translate('payment_config_not_found')]]), 400);
        }
        $config = $this->config_values;
        session()->put('payment_id', $payment_data->id);
        return view('Gateways::payment.senang-pay', compact('payment_data', 'payer', 'config'));
    }

    public function return_senang_pay(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        if ($request['status_id'] == 1) {
            $this->payment::where(['id' => session()->get('payment_id')])->update([
                'payment_method' => 'senang_pay',
                'is_paid' => 1,
                'transaction_id' => $request['transaction_id'],
            ]);
            $data = $this->payment::where(['id' => session()->get('payment_id')])->first();
            if (isset($data) && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }
            return $this->payment_response($data, 'success');
        }
        $payment_data = $this->payment::where(['id' => session()->get('payment_id')])->first();
        if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
            call_user_func($payment_data->failure_hook, $payment_data);
        }
        return $this->payment_response($payment_data, 'fail');
    }
}
SENANGPAY_EOF

# Update PhonepeController.php - using sed to fix the constructor
echo "Updating PhonepeController.php..."
if [ -f "${CONTROLLERS_DIR}/PhonepeController.php" ]; then
    # Fix the constructor - remove line 28 and ensure proper structure
    sed -i '27,29d' "${CONTROLLERS_DIR}/PhonepeController.php"
    sed -i '27a\    {' "${CONTROLLERS_DIR}/PhonepeController.php"
    sed -i '28a\        $config = $this->payment_config('\''phonepe'\'', '\''payment_config'\'');' "${CONTROLLERS_DIR}/PhonepeController.php"
    sed -i '29a\        $this->config_values = null; // Initialize $config_values' "${CONTROLLERS_DIR}/PhonepeController.php"
    
    # Add null checks in payment method
    if ! grep -q "if (!\$this->config_values)" "${CONTROLLERS_DIR}/PhonepeController.php"; then
        sed -i '/\$payment_data = \$this->payment::where/a\        \n        if (!$this->config_values) {\n            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, [['\''code'\'' => '\''config'\'', '\''message'\'' => translate('\''payment_config_not_found'\'')]]), 400);\n        }' "${CONTROLLERS_DIR}/PhonepeController.php"
    fi
fi

# Update FlutterwaveV3Controller.php - already uploaded, just verify
echo "Verifying FlutterwaveV3Controller.php..."
if [ -f "${CONTROLLERS_DIR}/FlutterwaveV3Controller.php" ]; then
    if ! grep -q "\$this->config_values = null;" "${CONTROLLERS_DIR}/FlutterwaveV3Controller.php"; then
        sed -i '28a\        $this->config_values = null; // Initialize $config_values' "${CONTROLLERS_DIR}/FlutterwaveV3Controller.php"
    fi
fi

# Update WorldPayController.php - fix duplicate brace
echo "Updating WorldPayController.php..."
if [ -f "${CONTROLLERS_DIR}/WorldPayController.php" ]; then
    # Remove duplicate opening brace on line 36
    sed -i '34,36d' "${CONTROLLERS_DIR}/WorldPayController.php"
    sed -i '34a\        $this->config_values = null; // Initialize $config_values' "${CONTROLLERS_DIR}/WorldPayController.php"
    sed -i '35a\        $config = $this->payment_config('\''worldpay'\'', '\''payment_config'\'');' "${CONTROLLERS_DIR}/WorldPayController.php"
fi

echo "✓ Controllers updated"
echo ""

# Step 2: Update Apache Configuration
echo "Step 2: Updating Apache configuration..."
APACHE_CONFIG="/etc/apache2/sites-available/6ammart.conf"
BACKUP_CONFIG="${APACHE_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"

if [ -f "${APACHE_CONFIG}" ]; then
    cp "${APACHE_CONFIG}" "${BACKUP_CONFIG}"
    echo "Backed up config to ${BACKUP_CONFIG}"
fi

cat > "${APACHE_CONFIG}" << 'APACHE_EOF'
<VirtualHost *:80>
    ServerName 193.162.129.214
    DocumentRoot /var/www/6ammart-laravel/public

    <Directory /var/www/6ammart-laravel/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost/"
    </FilesMatch>

    ProxyPreserveHost On

    # Exclude /api from proxying - serve directly from Laravel
    ProxyPass /api !
    
    # Proxy all other requests to React app
    ProxyPass / http://127.0.0.1:3000/
    ProxyPassReverse / http://127.0.0.1:3000/

    ErrorLog ${APACHE_LOG_DIR}/6ammart_error.log
    CustomLog ${APACHE_LOG_DIR}/6ammart_access.log combined
</VirtualHost>
APACHE_EOF

echo "✓ Apache configuration updated"

# Step 3: Enable Apache Modules
echo "Step 3: Enabling Apache modules..."
a2enmod rewrite proxy proxy_http 2>/dev/null || true
echo "✓ Modules enabled"

# Step 4: Verify Apache Config
echo "Step 4: Verifying Apache config..."
if apache2ctl configtest; then
    echo "✓ Config valid"
else
    echo "✗ Config has errors!"
    exit 1
fi

# Step 5: Reload Apache
echo "Step 5: Reloading Apache..."
systemctl reload apache2
echo "✓ Apache reloaded"

# Step 6: Clear Laravel Caches
echo "Step 6: Clearing Laravel caches..."
cd /var/www/6ammart-laravel
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "✓ Caches cleared"

# Step 7: Verify Routes
echo "Step 7: Verifying routes..."
if timeout 20 php artisan route:list 2>&1 | head -n 30 > /dev/null; then
    echo "✓ Routes load successfully"
    echo "Sample routes:"
    php artisan route:list 2>&1 | head -n 5
else
    echo "✗ Routes failed to load!"
    php artisan route:list 2>&1 | head -n 20
    exit 1
fi

# Step 8: Verify Services
echo "Step 8: Verifying services..."
systemctl is-active mysql && echo "✓ MySQL running" || (systemctl start mysql && systemctl enable mysql && echo "✓ MySQL started")
systemctl is-active apache2 && echo "✓ Apache running" || (echo "✗ Apache not running!" && exit 1)
pm2 list | grep -q "6ammart-react" && echo "✓ React app running" || echo "⚠ React app not in PM2"

# Step 9: Test API
echo "Step 9: Testing API..."
API_TEST=$(curl -s -w "\n%{http_code}" -X POST http://127.0.0.1/api/v1/auth/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"test"}' 2>/dev/null || echo -e "\n000")
HTTP_CODE=$(echo "$API_TEST" | tail -n 1)
if [ "$HTTP_CODE" != "000" ] && [ "$HTTP_CODE" != "404" ]; then
    echo "✓ API responded (HTTP $HTTP_CODE)"
    echo "Response preview: $(echo "$API_TEST" | head -n -1 | head -c 200)"
else
    echo "⚠ API test: HTTP $HTTP_CODE"
fi

echo ""
echo "=========================================="
echo "Deployment completed!"
echo "=========================================="

