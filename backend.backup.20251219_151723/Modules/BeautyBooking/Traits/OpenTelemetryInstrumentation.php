<?php

declare(strict_types=1);

namespace Modules\BeautyBooking\Traits;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\API\Globals;

/**
 * OpenTelemetry Instrumentation Trait
 * Trait ابزارسازی OpenTelemetry
 * 
 * Provides OpenTelemetry instrumentation methods for Beauty Booking module
 * ارائه متدهای ابزارسازی OpenTelemetry برای ماژول Beauty Booking
 */
trait OpenTelemetryInstrumentation
{
    /**
     * Check if OpenTelemetry is enabled
     * بررسی فعال بودن OpenTelemetry
     *
     * @return bool
     */
    protected function isOpenTelemetryEnabled(): bool
    {
        return config('opentelemetry.enabled', false) 
            && config('opentelemetry.beauty_booking.enabled', true);
    }

    /**
     * Start a new span for a Beauty Booking operation
     * شروع span جدید برای عملیات Beauty Booking
     *
     * @param string $name Span name
     * @param array $attributes Span attributes
     * @param int $kind Span kind (default: INTERNAL)
     * @return \OpenTelemetry\API\Trace\SpanInterface|null
     */
    protected function startBeautyBookingSpan(
        string $name,
        array $attributes = [],
        int $kind = SpanKind::KIND_INTERNAL
    ) {
        if (!$this->isOpenTelemetryEnabled()) {
            return null;
        }

        try {
            // Get tracer provider from container or Globals
            // دریافت tracer provider از container یا Globals
            $tracerProvider = app()->bound('opentelemetry.tracer_provider') 
                ? app('opentelemetry.tracer_provider')
                : null;
            
            if (!$tracerProvider) {
                // Try to get from Globals (may not work if not initialized)
                // تلاش برای دریافت از Globals (ممکن است کار نکند اگر راه‌اندازی نشده باشد)
                try {
                    $tracerProvider = Globals::tracerProvider();
                } catch (\Exception $e) {
                    \Log::warning('OpenTelemetry tracer provider not available: ' . $e->getMessage());
                    return null;
                }
            }
            
            $tracer = $tracerProvider->getTracer('beauty-booking-module');
            $span = $tracer->spanBuilder($name)
                ->setSpanKind($kind)
                ->startSpan();

            // Add default attributes
            // افزودن ویژگی‌های پیش‌فرض
            $span->setAttributes([
                'beauty.booking.module' => 'BeautyBooking',
                'beauty.booking.operation' => $name,
            ]);

            // Add custom attributes
            // افزودن ویژگی‌های سفارشی
            if (!empty($attributes)) {
                $span->setAttributes($attributes);
            }

            return $span;
        } catch (\Exception $e) {
            \Log::warning('Failed to create OpenTelemetry span: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * End a span with optional error
     * پایان span با خطای اختیاری
     *
     * @param mixed $span The span to end
     * @param \Throwable|null $exception Optional exception
     * @return void
     */
    protected function endSpan($span, ?\Throwable $exception = null): void
    {
        if (!$span) {
            return;
        }

        try {
            if ($exception) {
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                $span->recordException($exception);
            } else {
                $span->setStatus(StatusCode::STATUS_OK);
            }

            $span->end();
        } catch (\Exception $e) {
            \Log::warning('Failed to end OpenTelemetry span: ' . $e->getMessage());
        }
    }

    /**
     * Add event to current span
     * افزودن رویداد به span فعلی
     *
     * @param string $name Event name
     * @param array $attributes Event attributes
     * @return void
     */
    protected function addSpanEvent(string $name, array $attributes = []): void
    {
        if (!$this->isOpenTelemetryEnabled()) {
            return;
        }

        try {
            // Get tracer provider from container
            // دریافت tracer provider از container
            $tracerProvider = app()->bound('opentelemetry.tracer_provider') 
                ? app('opentelemetry.tracer_provider')
                : null;
            
            if (!$tracerProvider) {
                return;
            }
            
            $span = $tracerProvider
                ->getTracer('beauty-booking-module')
                ->spanBuilder('current')
                ->startSpan();

            $span->addEvent($name, $attributes);
            $span->end();
        } catch (\Exception $e) {
            \Log::warning('Failed to add OpenTelemetry event: ' . $e->getMessage());
        }
    }

    /**
     * Instrument a callback with a span
     * ابزارسازی یک callback با span
     *
     * @param string $spanName Span name
     * @param callable $callback Callback to execute
     * @param array $attributes Span attributes
     * @return mixed
     */
    protected function instrument(string $spanName, callable $callback, array $attributes = [])
    {
        if (!$this->isOpenTelemetryEnabled()) {
            return $callback();
        }

        $span = $this->startBeautyBookingSpan($spanName, $attributes);

        try {
            $result = $callback();
            $this->endSpan($span);
            return $result;
        } catch (\Throwable $e) {
            $this->endSpan($span, $e);
            throw $e;
        }
    }

    /**
     * Add booking-specific attributes to span
     * افزودن ویژگی‌های خاص رزرو به span
     *
     * @param mixed $span The span
     * @param array $bookingData Booking data
     * @return void
     */
    protected function addBookingAttributes($span, array $bookingData): void
    {
        if (!$span) {
            return;
        }

        try {
            $attributes = [
                'beauty.booking.salon_id' => $bookingData['salon_id'] ?? null,
                'beauty.booking.service_id' => $bookingData['service_id'] ?? null,
                'beauty.booking.staff_id' => $bookingData['staff_id'] ?? null,
                'beauty.booking.user_id' => $bookingData['user_id'] ?? null,
            ];

            // Remove null values
            // حذف مقادیر null
            $attributes = array_filter($attributes, fn($value) => $value !== null);

            $span->setAttributes($attributes);
        } catch (\Exception $e) {
            \Log::warning('Failed to add booking attributes: ' . $e->getMessage());
        }
    }
}

