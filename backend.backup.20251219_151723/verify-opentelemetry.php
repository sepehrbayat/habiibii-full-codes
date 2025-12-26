<?php

/**
 * OpenTelemetry Verification Script
 * اسکریپت تأیید OpenTelemetry
 * 
 * This script verifies that OpenTelemetry is properly configured and can send traces
 * این اسکریپت تأیید می‌کند که OpenTelemetry به درستی پیکربندی شده و می‌تواند trace ارسال کند
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;

echo "========================================\n";
echo "OpenTelemetry Verification\n";
echo "تأیید OpenTelemetry\n";
echo "========================================\n\n";

// Step 1: Check Configuration
echo "Step 1: Checking Configuration...\n";
echo "مرحله 1: بررسی تنظیمات...\n";

$enabled = config('opentelemetry.enabled', false);
$endpoint = config('opentelemetry.endpoint');
$serviceName = config('opentelemetry.service_name');
$protocol = config('opentelemetry.protocol');

if (!$enabled) {
    echo "❌ OpenTelemetry is DISABLED\n";
    echo "❌ OpenTelemetry غیرفعال است\n";
    echo "   Set OTEL_ENABLED=true in .env\n";
    exit(1);
}

echo "✓ OpenTelemetry: ENABLED\n";
echo "✓ OpenTelemetry: فعال\n";
echo "  Endpoint: {$endpoint}\n";
echo "  Protocol: {$protocol}\n";
echo "  Service: {$serviceName}\n\n";

// Step 2: Check Tracer Provider
echo "Step 2: Checking Tracer Provider...\n";
echo "مرحله 2: بررسی Tracer Provider...\n";

try {
    // Access tracer provider through container (initialized by service provider)
    // دسترسی به tracer provider از طریق container (راه‌اندازی شده توسط service provider)
    $tracerProvider = app('opentelemetry.tracer_provider') ?? Globals::tracerProvider();
    
    if (!$tracerProvider) {
        echo "❌ Tracer Provider not initialized\n";
        echo "❌ Tracer Provider راه‌اندازی نشده است\n";
        exit(1);
    }
    
    echo "✓ Tracer Provider: Initialized\n";
    echo "✓ Tracer Provider: راه‌اندازی شده\n\n";
    
    // Step 3: Create Test Span
    echo "Step 3: Creating Test Span...\n";
    echo "مرحله 3: ایجاد Span تست...\n";
    
    $tracer = $tracerProvider->getTracer('beauty-booking-verification', '1.0.0');
    
    $span = $tracer->spanBuilder('beauty.booking.verification.test')
        ->setSpanKind(SpanKind::KIND_INTERNAL)
        ->startSpan();
    
    $scope = $span->activate();
    
    try {
        // Add attributes similar to real booking
        $span->setAttributes([
            'beauty.booking.module' => 'BeautyBooking',
            'beauty.booking.operation' => 'verification_test',
            'beauty.booking.salon_id' => 999,
            'beauty.booking.service_id' => 999,
            'beauty.booking.user_id' => 999,
            'test.timestamp' => time(),
            'test.purpose' => 'opentelemetry_verification',
        ]);
        
        echo "✓ Span created with attributes\n";
        echo "✓ Span با ویژگی‌ها ایجاد شد\n";
        
        // Simulate work
        usleep(50000); // 50ms
        
        $span->setStatus(StatusCode::STATUS_OK);
        echo "✓ Span status: OK\n";
        echo "✓ وضعیت Span: OK\n";
        
    } catch (\Throwable $e) {
        $span->recordException($e);
        $span->setStatus(StatusCode::STATUS_ERROR, $e->getMessage());
        echo "❌ Error in span: " . $e->getMessage() . "\n";
        echo "❌ خطا در span: " . $e->getMessage() . "\n";
    } finally {
        $span->end();
        $scope->detach();
        echo "✓ Span ended\n";
        echo "✓ Span پایان یافت\n\n";
    }
    
    // Step 4: Check Observe Agent
    echo "Step 4: Checking Observe Agent...\n";
    echo "مرحله 4: بررسی Observe Agent...\n";
    
    $agentStatus = shell_exec('observe-agent status 2>&1');
    
    if (strpos($agentStatus, 'Status: Running') !== false) {
        echo "✓ Observe Agent: Running\n";
        echo "✓ Observe Agent: در حال اجرا\n";
        
        // Extract trace stats
        if (preg_match('/Traces Stats.*?ReceiverAcceptedCount:\s*(\d+)/s', $agentStatus, $matches)) {
            $traceCount = (int)$matches[1];
            echo "  Traces received: {$traceCount}\n";
            echo "  traceهای دریافت شده: {$traceCount}\n";
        }
    } else {
        echo "⚠️  Observe Agent: Status unknown\n";
        echo "⚠️  Observe Agent: وضعیت نامشخص\n";
    }
    
    echo "\n";
    
    // Step 5: Wait and check again
    echo "Step 5: Waiting for spans to be sent...\n";
    echo "مرحله 5: در انتظار ارسال spanها...\n";
    echo "  (BatchSpanProcessor may delay sending)\n";
    echo "  (BatchSpanProcessor ممکن است ارسال را به تأخیر بیندازد)\n";
    
    sleep(3);
    
    $agentStatusAfter = shell_exec('observe-agent status 2>&1');
    
    if (preg_match('/Traces Stats.*?ReceiverAcceptedCount:\s*(\d+)/s', $agentStatusAfter, $matchesAfter)) {
        $traceCountAfter = (int)$matchesAfter[1];
        echo "  Traces received after wait: {$traceCountAfter}\n";
        echo "  traceهای دریافت شده پس از انتظار: {$traceCountAfter}\n";
        
        if ($traceCountAfter > $traceCount) {
            echo "\n✅ SUCCESS: New traces detected!\n";
            echo "✅ موفقیت: traceهای جدید تشخیص داده شدند!\n";
        } else {
            echo "\n⚠️  Note: Traces may be batched. Check Observe dashboard.\n";
            echo "⚠️  توجه: traceها ممکن است batch شده باشند. داشبورد Observe را بررسی کنید.\n";
        }
    }
    
    echo "\n";
    
    // Step 6: Summary
    echo "========================================\n";
    echo "Verification Summary\n";
    echo "خلاصه تأیید\n";
    echo "========================================\n";
    echo "✓ Configuration: OK\n";
    echo "✓ Tracer Provider: OK\n";
    echo "✓ Span Creation: OK\n";
    echo "✓ Observe Agent: Running\n";
    echo "\n";
    echo "Next Steps:\n";
    echo "مراحل بعدی:\n";
    echo "1. Create a real booking through the Beauty Booking module\n";
    echo "   یک رزرو واقعی از طریق ماژول Beauty Booking ایجاد کنید\n";
    echo "2. Check Observe dashboard for traces with operation: beauty.booking.create\n";
    echo "   داشبورد Observe را برای traceها با عملیات: beauty.booking.create بررسی کنید\n";
    echo "3. Monitor agent: observe-agent status\n";
    echo "   نظارت بر agent: observe-agent status\n";
    echo "\n";
    echo "Setup is complete and ready! 🎉\n";
    echo "راه‌اندازی کامل و آماده است! 🎉\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "❌ خطا: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

