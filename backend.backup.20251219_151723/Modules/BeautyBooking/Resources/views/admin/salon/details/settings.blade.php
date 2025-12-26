<!-- Settings Tab -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">{{ translate('messages.settings') }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ translate('messages.Verification_Status') }}</label>
                    <div>
                        @if($salon->verification_status == 1)
                            <span class="badge badge-success">{{ translate('Approved') }}</span>
                        @elseif($salon->verification_status == 2)
                            <span class="badge badge-danger">{{ translate('Rejected') }}</span>
                        @else
                            <span class="badge badge-warning">{{ translate('Pending') }}</span>
                        @endif
                    </div>
                </div>
                
                @if($salon->verification_notes)
                    <div class="form-group">
                        <label>{{ translate('messages.Verification_Notes') }}</label>
                        <p class="text-muted">{{ $salon->verification_notes }}</p>
                    </div>
                @endif

                @if($salon->verification_status != 1)
                    <div class="form-group">
                        <form action="{{ route('admin.beautybooking.salon.approve', $salon->id) }}" method="POST">
                            @csrf
                            <label for="approve_notes">{{ translate('Approval Notes') }} ({{ translate('Optional') }})</label>
                            <textarea name="verification_notes" id="approve_notes" class="form-control" rows="3" placeholder="{{ translate('Add optional notes for approval') }}">{{ old('verification_notes') }}</textarea>
                            <button type="submit" class="btn btn--primary mt-2">
                                <i class="tio-checkmark-circle"></i> {{ translate('Approve Salon') }}
                            </button>
                        </form>
                    </div>
                @endif

                @if($salon->verification_status != 2)
                    <div class="form-group">
                        <form action="{{ route('admin.beautybooking.salon.reject', $salon->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to reject this salon?') }}');">
                            @csrf
                            <label for="reject_notes">{{ translate('Rejection Notes') }} <span class="text-danger">*</span></label>
                            <textarea name="verification_notes" id="reject_notes" class="form-control @error('verification_notes') is-invalid @enderror" rows="3" required placeholder="{{ translate('Please provide reason for rejection') }}">{{ old('verification_notes') }}</textarea>
                            @error('verification_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="btn btn--danger mt-2">
                                <i class="tio-clear-circle"></i> {{ translate('Reject Salon') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>{{ translate('messages.Is_Featured') }}</label>
                    <div>
                        <span class="badge badge-{{ $salon->is_featured ? 'success' : 'secondary' }}">
                            {{ $salon->is_featured ? translate('Yes') : translate('No') }}
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ translate('messages.Is_Verified') }}</label>
                    <div>
                        <span class="badge badge-{{ $salon->is_verified ? 'success' : 'secondary' }}">
                            {{ $salon->is_verified ? translate('Yes') : translate('No') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

