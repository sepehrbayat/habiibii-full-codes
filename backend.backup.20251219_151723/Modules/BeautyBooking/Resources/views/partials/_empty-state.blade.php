{{-- Empty State Partial
    حالت خالی
    Reusable empty state component for when no data is available
    کامپوننت قابل استفاده مجدد برای حالت خالی در صورت نبود داده
--}}

@props([
    'icon' => 'inbox', // Font Awesome icon name
    'title' => null,
    'message' => null,
    'action' => null, // ['label' => '...', 'url' => '...', 'class' => '...']
    'size' => 'medium' // small, medium, large
])

@php
    $displayTitle = $title ?? translate('messages.no_data_available') ?? 'No Data Available';
    $displayMessage = $message ?? translate('messages.no_items_found') ?? 'No items found.';
    $sizeClass = $size === 'large' ? 'fa-5x' : ($size === 'small' ? 'fa-2x' : 'fa-4x');
@endphp

<div class="beauty-empty-state text-center py-5">
    <div class="beauty-empty-state-icon mb-3">
        <i class="fas fa-{{ $icon }} {{ $sizeClass }} text-muted"></i>
    </div>
    
    <h4 class="beauty-empty-state-title mb-2">{{ $displayTitle }}</h4>
    
    @if($displayMessage)
        <p class="beauty-empty-state-message text-muted mb-4">{{ $displayMessage }}</p>
    @endif
    
    @if($action && isset($action['url']))
        <a href="{{ $action['url'] }}" 
           class="btn {{ $action['class'] ?? 'btn-primary' }}">
            <i class="fas fa-plus mr-2"></i>
            {{ $action['label'] ?? translate('messages.add_new') ?? 'Add New' }}
        </a>
    @endif
</div>

@push('css')
<style>
    .beauty-empty-state {
        padding: 3rem 1rem;
        min-height: 300px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .beauty-empty-state-icon {
        opacity: 0.5;
    }
    
    .beauty-empty-state-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #495057;
    }
    
    .beauty-empty-state-message {
        font-size: 1rem;
        max-width: 500px;
        margin: 0 auto;
    }
    
    @media (max-width: 768px) {
        .beauty-empty-state {
            padding: 2rem 1rem;
            min-height: 200px;
        }
        
        .beauty-empty-state-title {
            font-size: 1.25rem;
        }
        
        .beauty-empty-state-message {
            font-size: 0.9rem;
        }
    }
</style>
@endpush

