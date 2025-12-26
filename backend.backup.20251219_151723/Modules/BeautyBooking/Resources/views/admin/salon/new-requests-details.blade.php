@extends('layouts.admin.app')

@section('title',$salon->store->name ?? translate('Salon Request Details'))

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{asset('public/assets/admin/css/croppie.css')}}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">

        @include('beautybooking::admin.salon.details.partials._header',['salon'=>$salon])

        <!-- Request Status Alert -->
        @if($salon->verification_status == 0)
            <div class="alert alert-warning mb-3">
                <div class="d-flex align-items-center">
                    <i class="tio-info-outlined mr-2"></i>
                    <span>{{ translate('messages.This is a pending salon registration request. Please review the details and documents before approving or rejecting.') }}</span>
                </div>
            </div>
        @elseif($salon->verification_status == 2)
            <div class="alert alert-danger mb-3">
                <div class="d-flex align-items-center">
                    <i class="tio-clear-outlined mr-2"></i>
                    <span>{{ translate('messages.This request has been rejected.') }}</span>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="btn--container justify-content-end">
                    @if($salon->verification_status == 0)
                        <button type="button"
                                class="btn btn--primary"
                                data-toggle="modal"
                                data-target="#approveModal">
                            <i class="tio-done mr-1"></i>
                            {{ translate('Approve Request') }}
                        </button>
                        <button type="button"
                                class="btn btn--danger"
                                data-toggle="modal"
                                data-target="#rejectModal">
                            <i class="tio-clear mr-1"></i>
                            {{ translate('Reject Request') }}
                        </button>
                    @endif
                    <a href="{{ route('admin.beautybooking.salon.new-requests') }}" class="btn btn--secondary">
                        <i class="tio-arrow-back mr-1"></i>
                        {{ translate('Back to Requests') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Overview Section -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">{{ translate('messages.Salon_Information') }}</h5>
            </div>
            <div class="card-body">
                @include('beautybooking::admin.salon.details.overview')
            </div>
        </div>

        <!-- Documents Section -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">{{ translate('messages.Documents') }}</h5>
            </div>
            <div class="card-body">
                @include('beautybooking::admin.salon.details.documents')
            </div>
        </div>

        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body pt-5 p-md-5">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <img src="{{ asset('public/assets/admin/img/new-img/close-icon-dark.svg') }}" alt="">
                        </button>

                        <div class="d-flex justify-content-center mb-4">
                            <img width="75" height="75" src="{{ asset('public/assets/admin/img/modal/mark.png') }}"
                                 class="rounded-circle" alt="">
                        </div>

                        <h3 class="text--title mb-6 font-medium text-center">
                            {{ translate('Are you sure, want to approve this salon request?') }}</h3>
                        <form method="get" action="{{route('admin.beautybooking.salon.approve-or-deny',[$salon['id'],1])}}">
                            @csrf
                            <div class="form-floating">
                                <input type="hidden" value="1" name="status">
                                <div class="d-flex justify-content-center gap-3">
                                    <button type="button" data-dismiss="modal" aria-label="Close"
                                            class="btn btn--reset">{{ translate('Cancel') }}</button>
                                    <button type="submit" class="btn btn--primary">{{ translate('Approve') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body pt-5 p-md-5">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <img src="{{ asset('public/assets/admin/img/new-img/close-icon-dark.svg') }}" alt="">
                        </button>

                        <div class="d-flex justify-content-center mb-4">
                            <img width="75" height="75" src="{{ asset('public/assets/admin/img/icons/delete.png') }}"
                                 class="rounded-circle" alt="">
                        </div>

                        <h3 class="text--title mb-6 font-medium text-center">
                            {{ translate('Are you sure, want to reject this salon request?') }}</h3>
                        <form method="get" action="{{route('admin.beautybooking.salon.approve-or-deny',[$salon['id'],0])}}">
                            @csrf
                            <div class="form-floating">
                                <label for="rejection-note" class="font-medium input-label text--title">{{ translate('Rejection Note') }}
                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="Rejection Note">
                                            <i class="tio-info text--title opacity-60"></i>
                                    </span>
                                </label>
                                <div class="mb-30">
                                    <textarea
                                        class="form-control h--90"
                                        placeholder="{{ translate('Type your Rejection Note') }}"
                                        name="message"
                                        id="rejection-note"
                                        maxlength="200"
                                        required
                                    ></textarea>
                                    <div id="char-count-reject">0/200</div>
                                </div>
                                <input type="hidden" value="0" name="status">
                                <div class="d-flex justify-content-end gap-3">
                                    <button type="button" data-dismiss="modal" aria-label="Close"
                                            class="btn btn--reset">{{ translate('Cancel') }}</button>
                                    <button type="submit" class="btn btn--primary">{{ translate('Reject') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    $(document).ready(function() {
        // Character counter for rejection note
        $('#rejection-note').on('input', function() {
            const length = $(this).val().length;
            $('#char-count-reject').text(length + '/200');
        });
    });
</script>
@endpush

