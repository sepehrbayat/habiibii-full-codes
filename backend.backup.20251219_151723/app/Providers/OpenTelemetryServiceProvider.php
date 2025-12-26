<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\Contrib\Otlp\SpanExporterFactory;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\API\Globals;

/**
 * OpenTelemetry Service Provider
 * سرویس پروایدر OpenTelemetry برای ابزارسازی و رصد برنامه
 * 
 * This provider initializes OpenTelemetry instrumentation to send
 * traces, metrics, and logs to the Observe Agent
 * این پروایدر ابزارسازی OpenTelemetry را برای ارسال
 * traceها، متریک‌ها و لاگ‌ها به Observe Agent راه‌اندازی می‌کند
 */
class OpenTelemetryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     * ثبت سرویس‌ها
     *
     * @return void
     */
    public function register(): void
    {
        // Only initialize if OpenTelemetry is enabled
        // فقط در صورت فعال بودن OpenTelemetry راه‌اندازی شود
        if (!config('opentelemetry.enabled', false)) {
            return;
        }

        $this->initializeOpenTelemetry();
    }

    /**
     * Bootstrap services.
     * راه‌اندازی سرویس‌ها
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Initialize OpenTelemetry SDK
     * راه‌اندازی SDK OpenTelemetry
     *
     * @return void
     */
    protected function initializeOpenTelemetry(): void
    {
        try {
            // Get configuration
            // دریافت تنظیمات
            $endpoint = config('opentelemetry.endpoint', 'http://localhost:4317');
            $serviceName = config('opentelemetry.service_name', '6ammart-laravel');
            $environment = config('opentelemetry.environment', env('APP_ENV', 'production'));
            $team = config('opentelemetry.team', 'default');

            // Create resource with service attributes
            // ایجاد Resource با ویژگی‌های سرویس
            $attributes = Attributes::create([
                ResourceAttributes::SERVICE_NAME => $serviceName,
                'deployment.environment.name' => $environment,
                'team.name' => $team,
            ]);
            
            $resource = ResourceInfo::create($attributes);

            // Create OTLP exporter using factory (reads from environment variables)
            // ایجاد OTLP exporter با استفاده از factory (از متغیرهای محیطی می‌خواند)
            $exporterFactory = new SpanExporterFactory();
            $exporter = $exporterFactory->create();

            // Create span processor
            // ایجاد پردازش‌گر span
            $clock = ClockFactory::getDefault();
            $spanProcessor = new BatchSpanProcessor($exporter, $clock);

            // Create tracer provider
            // ایجاد ارائه‌دهنده tracer
            $tracerProvider = TracerProvider::builder()
                ->addSpanProcessor($spanProcessor)
                ->setResource($resource)
                ->build();

            // Set global tracer provider
            // تنظیم ارائه‌دهنده tracer سراسری
            Globals::registerInitializer(function () use ($tracerProvider) {
                return $tracerProvider;
            });
            
            // Store tracer provider in container for later use
            // ذخیره tracer provider در container برای استفاده بعدی
            $this->app->singleton('opentelemetry.tracer_provider', function () use ($tracerProvider) {
                return $tracerProvider;
            });
            
            \Log::info('OpenTelemetry initialized successfully', [
                'endpoint' => $endpoint,
                'service_name' => $serviceName,
                'environment' => $environment,
            ]);

        } catch (\Exception $e) {
            // Log error but don't break the application
            // ثبت خطا اما بدون شکستن برنامه
            \Log::error('OpenTelemetry initialization failed: ' . $e->getMessage());
        }
    }
}

