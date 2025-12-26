<?php

declare(strict_types=1);

/**
 * OpenTelemetry Configuration
 * تنظیمات OpenTelemetry
 * 
 * Configuration for OpenTelemetry instrumentation
 * تنظیمات برای ابزارسازی OpenTelemetry
 */

return [
    /*
    |--------------------------------------------------------------------------
    | OpenTelemetry Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable OpenTelemetry instrumentation
    | فعال یا غیرفعال کردن ابزارسازی OpenTelemetry
    |
    */
    'enabled' => env('OTEL_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | OTLP Endpoint
    |--------------------------------------------------------------------------
    |
    | The endpoint URL for the Observe Agent
    | URL endpoint برای Observe Agent
    |
    | For gRPC: http://localhost:4317
    | For HTTP/Protobuf: http://localhost:4318
    |
    */
    'endpoint' => env('OTEL_EXPORTER_OTLP_ENDPOINT', 'http://localhost:4317'),

    /*
    |--------------------------------------------------------------------------
    | OTLP Protocol
    |--------------------------------------------------------------------------
    |
    | The protocol to use for OTLP export
    | پروتکل مورد استفاده برای export OTLP
    |
    | Options: 'grpc', 'http/protobuf', 'http/json'
    |
    */
    'protocol' => env('OTEL_EXPORTER_OTLP_PROTOCOL', 'grpc'),

    /*
    |--------------------------------------------------------------------------
    | Service Name
    |--------------------------------------------------------------------------
    |
    | The name of the service being instrumented
    | نام سرویس در حال ابزارسازی
    |
    */
    'service_name' => env('OTEL_SERVICE_NAME', env('OTEL_RESOURCE_ATTRIBUTES_SERVICE_NAME', '6ammart-laravel')),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | The deployment environment (e.g., production, staging, development)
    | محیط استقرار (مثلاً production، staging، development)
    |
    */
    'environment' => env('OTEL_ENVIRONMENT', env('OTEL_RESOURCE_ATTRIBUTES_DEPLOYMENT_ENVIRONMENT_NAME', env('APP_ENV', 'production'))),

    /*
    |--------------------------------------------------------------------------
    | Team Name
    |--------------------------------------------------------------------------
    |
    | The team responsible for this service
    | تیم مسئول این سرویس
    |
    */
    'team' => env('OTEL_TEAM', env('OTEL_RESOURCE_ATTRIBUTES_TEAM_NAME', 'default')),

    /*
    |--------------------------------------------------------------------------
    | Beauty Booking Module Instrumentation
    |--------------------------------------------------------------------------
    |
    | Enable specific instrumentation for Beauty Booking module
    | فعال کردن ابزارسازی خاص برای ماژول Beauty Booking
    |
    */
    'beauty_booking' => [
        'enabled' => env('OTEL_BEAUTY_BOOKING_ENABLED', true),
        'trace_queries' => env('OTEL_BEAUTY_BOOKING_TRACE_QUERIES', true),
        'trace_requests' => env('OTEL_BEAUTY_BOOKING_TRACE_REQUESTS', true),
        'trace_services' => env('OTEL_BEAUTY_BOOKING_TRACE_SERVICES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sampling Rate
    |--------------------------------------------------------------------------
    |
    | The percentage of traces to sample (0.0 to 1.0)
    | درصد traceهای نمونه‌برداری شده (0.0 تا 1.0)
    |
    | 1.0 = 100% (sample all traces)
    | 0.1 = 10% (sample 10% of traces)
    |
    */
    'sampling_rate' => env('OTEL_SAMPLING_RATE', 1.0),

    /*
    |--------------------------------------------------------------------------
    | Batch Size
    |--------------------------------------------------------------------------
    |
    | Maximum number of spans to batch before sending
    | حداکثر تعداد span برای batch قبل از ارسال
    |
    */
    'batch_size' => env('OTEL_BATCH_SIZE', 512),

    /*
    |--------------------------------------------------------------------------
    | Export Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time to wait for export to complete (in seconds)
    | حداکثر زمان انتظار برای تکمیل export (به ثانیه)
    |
    */
    'export_timeout' => env('OTEL_EXPORT_TIMEOUT', 30),
];

