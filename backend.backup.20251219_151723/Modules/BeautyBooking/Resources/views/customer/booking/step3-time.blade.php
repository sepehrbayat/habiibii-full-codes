@if(isset($availableSlots) && count($availableSlots) > 0)
    <div class="row g-2">
        @foreach($availableSlots as $slot)
            <div class="col-md-3 col-sm-4">
                <label class="btn btn-outline-primary w-100 time-slot-btn" style="cursor: pointer;">
                    <input type="radio" name="booking_time" value="{{ $slot }}" required>
                    {{ $slot }}
                </label>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-warning">
        {{ translate('No available time slots for the selected date. Please choose another date.') }}
    </div>
@endif

<script>
    // Style time slot buttons
    document.querySelectorAll('.time-slot-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });
</script>

