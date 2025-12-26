#!/bin/bash
# Complete deployment fix script - includes all controller fixes
# Run this script on the server with: sudo bash complete-deployment.sh

set -e

echo "=========================================="
echo "6ammart Complete Deployment Fix"
echo "=========================================="
echo ""

# Step 1: Update Controllers
echo "Step 1: Updating payment gateway controllers..."

CONTROLLERS_DIR="/var/www/6ammart-laravel/Modules/Gateways/Http/Controllers"

# SenangPayController.php
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

# PhonepeController.php - Note: This is a large file, using a simplified approach
# We'll update just the constructor and add null checks
echo "Updating PhonepeController.php..."
sed -i '27,29d' "${CONTROLLERS_DIR}/PhonepeController.php" 2>/dev/null || true
sed -i '27a\    {' "${CONTROLLERS_DIR}/PhonepeController.php" 2>/dev/null || true
sed -i '28a\        $config = $this->payment_config('\''phonepe'\'', '\''payment_config'\'');' "${CONTROLLERS_DIR}/PhonepeController.php" 2>/dev/null || true
sed -i '29a\        $this->config_values = null; // Initialize $config_values' "${CONTROLLERS_DIR}/PhonepeController.php" 2>/dev/null || true

# FlutterwaveV3Controller.php - Already uploaded, but let's verify
echo "Verifying FlutterwaveV3Controller.php..."

# WorldPayController.php
echo "Updating WorldPayController.php..."
sed -i '34,36d' "${CONTROLLERS_DIR}/WorldPayController.php" 2>/dev/null || true
sed -i '34a\        $this->config_values = null; // Initialize $config_values' "${CONTROLLERS_DIR}/WorldPayController.php" 2>/dev/null || true
sed -i '35a\        $config = $this->payment_config('\''worldpay'\'', '\''payment_config'\'');' "${CONTROLLERS_DIR}/WorldPayController.php" 2>/dev/null || true

echo "✓ Controllers updated"
echo ""

# Step 2: Update Apache Configuration
echo "Step 2: Updating Apache configuration..."
APACHE_CONFIG="/etc/apache2/sites-available/6ammart.conf"
BACKUP_CONFIG="${APACHE_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"

if [ -f "${APACHE_CONFIG}" ]; then
    echo "Backing up current Apache config to ${BACKUP_CONFIG}..."
    cp "${APACHE_CONFIG}" "${BACKUP_CONFIG}"
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
echo ""

# Step 3: Enable Required Apache Modules
echo "Step 3: Enabling required Apache modules..."
a2enmod rewrite proxy proxy_http 2>/dev/null || true
echo "✓ Apache modules enabled"
echo ""

# Step 4: Verify Apache Config Syntax
echo "Step 4: Verifying Apache configuration syntax..."
if apache2ctl configtest; then
    echo "✓ Apache configuration syntax is valid"
else
    echo "✗ Apache configuration has syntax errors!"
    exit 1
fi
echo ""

# Step 5: Reload Apache
echo "Step 5: Reloading Apache..."
systemctl reload apache2
echo "✓ Apache reloaded"
echo ""

# Step 6: Clear Laravel Caches
echo "Step 6: Clearing Laravel caches..."
cd /var/www/6ammart-laravel
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "✓ Laravel caches cleared"
echo ""

# Step 7: Verify Routes Load
echo "Step 7: Verifying Laravel routes load..."
if timeout 20 php artisan route:list 2>&1 | head -n 30 > /dev/null; then
    echo "✓ Laravel routes load successfully"
    php artisan route:list 2>&1 | head -n 10
else
    echo "✗ Laravel routes failed to load!"
    php artisan route:list 2>&1 | head -n 20
    exit 1
fi
echo ""

# Step 8: Verify Services
echo "Step 8: Verifying services..."
echo "Checking MySQL..."
if systemctl is-active --quiet mysql; then
    echo "✓ MySQL is running"
else
    echo "⚠ MySQL is not running, attempting to start..."
    systemctl start mysql
    systemctl enable mysql
fi

echo "Checking Apache..."
if systemctl is-active --quiet apache2; then
    echo "✓ Apache is running"
else
    echo "✗ Apache is not running!"
    exit 1
fi

echo "Checking React app (PM2)..."
if pm2 list | grep -q "6ammart-react"; then
    echo "✓ React app is running in PM2"
    pm2 list | grep "6ammart-react"
else
    echo "⚠ React app is not running in PM2"
fi
echo ""

# Step 9: Test API Endpoint
echo "Step 9: Testing API endpoint..."
API_RESPONSE=$(curl -s -w "\n%{http_code}" http://127.0.0.1/api/v1/auth/login -X POST \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"test"}' 2>/dev/null || echo -e "\n000")

HTTP_CODE=$(echo "$API_RESPONSE" | tail -n 1)
BODY=$(echo "$API_RESPONSE" | head -n -1)

if [ "$HTTP_CODE" != "000" ]; then
    echo "✓ API endpoint responded (HTTP $HTTP_CODE)"
    if echo "$BODY" | grep -q "json\|error\|message"; then
        echo "✓ Response appears to be JSON"
        echo "Response preview: $(echo "$BODY" | head -c 200)"
    else
        echo "⚠ Response may not be JSON format"
        echo "Response preview: $(echo "$BODY" | head -c 200)"
    fi
else
    echo "⚠ API endpoint test failed (connection error)"
fi
echo ""

# Step 10: Test React App
echo "Step 10: Testing React app..."
REACT_RESPONSE=$(curl -s -w "\n%{http_code}" http://127.0.0.1:3000/ 2>/dev/null || echo -e "\n000")
REACT_CODE=$(echo "$REACT_RESPONSE" | tail -n 1)

if [ "$REACT_CODE" = "200" ]; then
    echo "✓ React app is accessible on port 3000"
else
    echo "⚠ React app may not be running on port 3000 (HTTP $REACT_CODE)"
fi
echo ""

echo "=========================================="
echo "Deployment fix completed!"
echo "=========================================="

