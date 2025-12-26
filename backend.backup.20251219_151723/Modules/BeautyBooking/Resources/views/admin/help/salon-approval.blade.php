@extends('layouts.admin.app')

@section('title', translate('messages.how_to_approve_salons'))

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="page-header-title text-break">
                    <span class="page-header-icon">
                        <img src="{{ asset('public/assets/admin/img/category.png') }}" class="w--22" alt="">
                    </span>
                    <span>{{ translate('messages.how_to_approve_salons') }}
                    </span>
                </h1>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <h3>{{ translate('messages.salon_approval_process') }}</h3>
                    <p>{{ translate('messages.salon_approval_process_description') }}</p>

                    <h4 class="mt-4">{{ translate('messages.step_1_review_documents') }}</h4>
                    <ol>
                        <li>{{ translate('messages.navigate_to_salons_page') }}</li>
                        <li>{{ translate('messages.click_on_salon_to_view_details') }}</li>
                        <li>{{ translate('messages.review_uploaded_documents') }}</li>
                        <li>{{ translate('messages.verify_license_number_and_expiry') }}</li>
                    </ol>

                    <h4 class="mt-4">{{ translate('messages.step_2_verify_information') }}</h4>
                    <ol>
                        <li>{{ translate('messages.check_business_type_salon_or_clinic') }}</li>
                        <li>{{ translate('messages.verify_store_information') }}</li>
                        <li>{{ translate('messages.check_working_hours') }}</li>
                        <li>{{ translate('messages.verify_contact_information') }}</li>
                    </ol>

                    <h4 class="mt-4">{{ translate('messages.step_3_approve_or_reject') }}</h4>
                    <ol>
                        <li>{{ translate('messages.click_approve_button_if_all_verified') }}</li>
                        <li>{{ translate('messages.click_reject_button_if_issues_found') }}</li>
                        <li>{{ translate('messages.add_verification_notes_if_needed') }}</li>
                        <li>{{ translate('messages.salon_will_receive_notification') }}</li>
                    </ol>

                    <h4 class="mt-4">{{ translate('messages.verification_statuses') }}</h4>
                    <ul>
                        <li><strong>{{ translate('messages.pending') }}:</strong> {{ translate('messages.pending_verification_description') }}</li>
                        <li><strong>{{ translate('messages.approved') }}:</strong> {{ translate('messages.approved_verification_description') }}</li>
                        <li><strong>{{ translate('messages.rejected') }}:</strong> {{ translate('messages.rejected_verification_description') }}</li>
                    </ul>

                    <h4 class="mt-4">{{ translate('messages.important_notes') }}</h4>
                    <ul>
                        <li>{{ translate('messages.verify_all_documents_before_approval') }}</li>
                        <li>{{ translate('messages.check_license_expiry_date') }}</li>
                        <li>{{ translate('messages.ensure_store_is_active') }}</li>
                        <li>{{ translate('messages.add_clear_notes_for_rejection') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

