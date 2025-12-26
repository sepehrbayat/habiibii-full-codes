@extends('layouts.app')

@section('title', translate('Write Review'))

@section('content')
<div class="container py-4">
    <h1 class="h2 mb-4">{{ translate('Write Review') }}</h1>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ translate('Booking Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <strong>{{ translate('Booking Reference') }}:</strong>
                            <p>{{ $booking->booking_reference }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ translate('Salon') }}:</strong>
                            <p>{{ $booking->salon->store->name ?? '' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ translate('Service') }}:</strong>
                            <p>{{ $booking->service->name ?? '' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ translate('Date') }}:</strong>
                            <p>{{ $booking->booking_date->format('Y-m-d') }} {{ $booking->booking_time }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ translate('Your Review') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('beauty-booking.reviews.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                        <!-- Rating -->
                        <div class="form-group mb-4">
                            <label class="form-label">{{ translate('Rating') }} <span class="text-danger">*</span></label>
                            <div class="rating-input">
                                @for($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" required {{ old('rating') == $i ? 'checked' : ($i == 5 ? 'checked' : '') }}>
                                    <label for="rating{{ $i }}" class="rating-star">
                                        <i class="tio-star{{ $i <= (old('rating') ?? 5) ? '' : '-outlined' }}"></i>
                                    </label>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Comment -->
                        <div class="form-group mb-4">
                            <label class="form-label" for="comment">{{ translate('Comment') }}</label>
                            <textarea 
                                name="comment" 
                                id="comment" 
                                class="form-control @error('comment') is-invalid @enderror" 
                                rows="5" 
                                placeholder="{{ translate('Share your experience...') }}"
                                maxlength="1000">{{ old('comment') }}</textarea>
                            <small class="form-text text-muted">{{ translate('Maximum 1000 characters') }}</small>
                            @error('comment')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Attachments -->
                        <div class="form-group mb-4">
                            <label class="form-label" for="attachments">{{ translate('Attachments') }} ({{ translate('Optional') }})</label>
                            <input 
                                type="file" 
                                name="attachments[]" 
                                id="attachments" 
                                class="form-control @error('attachments.*') is-invalid @enderror" 
                                multiple 
                                accept="image/jpeg,image/png,image/jpg">
                            <small class="form-text text-muted">{{ translate('You can upload multiple images (JPEG, PNG, JPG, max 2MB each)') }}</small>
                            @error('attachments.*')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Preview Images -->
                        <div id="image-preview" class="mb-4" style="display: none;">
                            <label class="form-label">{{ translate('Preview') }}:</label>
                            <div class="row g-2" id="preview-container"></div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="tio-checkmark"></i> {{ translate('Submit Review') }}
                            </button>
                            <a href="{{ route('beauty-booking.my-bookings.show', $booking->id) }}" class="btn btn-secondary">
                                {{ translate('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
    // Rating star interaction
    // تعامل ستاره رتبه‌بندی
    document.querySelectorAll('input[name="rating"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const rating = parseInt(this.value);
            document.querySelectorAll('.rating-star i').forEach(function(star, index) {
                const starValue = 5 - index;
                if (starValue <= rating) {
                    star.className = 'tio-star';
                } else {
                    star.className = 'tio-star-outlined';
                }
            });
        });
    });

    // Image preview
    // پیش‌نمایش تصویر
    document.getElementById('attachments').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('preview-container');
        const previewDiv = document.getElementById('image-preview');
        previewContainer.innerHTML = '';
        
        if (this.files.length > 0) {
            previewDiv.style.display = 'block';
            
            Array.from(this.files).forEach(function(file) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-md-3';
                        col.innerHTML = `
                            <div class="position-relative">
                                <img src="${e.target.result}" class="img-thumbnail" style="max-height: 150px; width: 100%; object-fit: cover;">
                            </div>
                        `;
                        previewContainer.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                }
            });
        } else {
            previewDiv.style.display = 'none';
        }
    });
</script>
<style>
    .rating-input {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 5px;
    }
    .rating-input input[type="radio"] {
        display: none;
    }
    .rating-input label {
        font-size: 2rem;
        color: #ffc107;
        cursor: pointer;
        transition: all 0.2s;
    }
    .rating-input label:hover,
    .rating-input label:hover ~ label {
        color: #ffc107;
    }
    .rating-input input[type="radio"]:checked ~ label {
        color: #ffc107;
    }
</style>
@endpush
@endsection

