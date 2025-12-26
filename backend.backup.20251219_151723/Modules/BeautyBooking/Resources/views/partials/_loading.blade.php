{{-- Loading Spinner Partial
    اسپینر بارگذاری
    Reusable loading indicator component
    کامپوننت قابل استفاده مجدد برای نشانگر بارگذاری
--}}

@props([
    'id' => 'loading',
    'size' => 'medium', // small, medium, large
    'text' => null,
    'overlay' => false, // Show overlay background
    'position' => 'center' // center, top, bottom
])

@php
    $sizeClasses = [
        'small' => 'spinner-border-sm',
        'medium' => '',
        'large' => 'spinner-border-lg'
    ];
    
    $sizeClass = $sizeClasses[$size] ?? '';
    $displayText = $text ?? translate('messages.loading') ?? 'Loading...';
@endphp

<div id="{{ $id }}" class="beauty-loading-indicator" style="display: none;">
    @if($overlay)
        <div class="beauty-loading-overlay"></div>
    @endif
    <div class="beauty-loading-content beauty-loading-{{ $position }}">
        <div class="spinner-border {{ $sizeClass }} text-primary" role="status">
            <span class="sr-only">{{ $displayText }}</span>
        </div>
        @if($displayText)
            <div class="beauty-loading-text mt-2">{{ $displayText }}</div>
        @endif
    </div>
</div>

@push('css')
<style>
    .beauty-loading-indicator {
        position: relative;
        z-index: 9999;
    }
    
    .beauty-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.3);
        z-index: 9998;
    }
    
    .beauty-loading-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .beauty-loading-center {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
    }
    
    .beauty-loading-top {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
    }
    
    .beauty-loading-bottom {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
    }
    
    .beauty-loading-text {
        color: #007bff;
        font-size: 14px;
        font-weight: 500;
    }
    
    .spinner-border-lg {
        width: 3rem;
        height: 3rem;
        border-width: 0.3rem;
    }
</style>
@endpush

