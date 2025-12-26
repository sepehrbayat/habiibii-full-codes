<div class="list-group">
    @if($staff->count() > 0)
        <label class="list-group-item">
            <input type="radio" name="staff_id" value="" checked>
            <div class="ms-3">
                <strong>{{ translate('No Preference') }}</strong>
                <div class="small text-muted">{{ translate('Any available staff member') }}</div>
            </div>
        </label>
        @foreach($staff as $staffMember)
            <label class="list-group-item">
                <input type="radio" name="staff_id" value="{{ $staffMember->id }}">
                <div class="ms-3">
                    <strong>{{ $staffMember->name }}</strong>
                    @if($staffMember->specializations && count($staffMember->specializations) > 0)
                        <div class="small text-muted">
                            {{ implode(', ', $staffMember->specializations) }}
                        </div>
                    @endif
                </div>
            </label>
        @endforeach
    @else
        <p class="text-muted">{{ translate('No staff members available for this service') }}</p>
        <input type="hidden" name="staff_id" value="">
    @endif
</div>

