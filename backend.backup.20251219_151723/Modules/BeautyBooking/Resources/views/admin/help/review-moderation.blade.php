@extends('layouts.admin.app')

@section('title', translate('messages.how_to_moderate_reviews'))

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="page-header-title text-break">
                    <span class="page-header-icon">
                        <img src="{{asset('public/assets/admin/img/beauty/review.png')}}" class="w--22" alt="">
                    </span>
                    <span>{{translate('messages.how_to_moderate_reviews')}}
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
                    <h3>{{ translate('messages.review_moderation_overview') }}</h3>
                    <p>{{ translate('messages.review_moderation_description') }}</p>

                    <h4 class="mt-4">{{ translate('messages.review_moderation_process') }}</h4>
                    <ol>
                        <li>{{ translate('messages.navigate_to_reviews_page') }}</li>
                        <li>{{ translate('messages.view_pending_reviews') }}</li>
                        <li>{{ translate('messages.read_review_content') }}</li>
                        <li>{{ translate('messages.check_review_attachments') }}</li>
                        <li>{{ translate('messages.approve_or_reject_review') }}</li>
                    </ol>

                    <h4 class="mt-4">{{ translate('messages.review_statuses') }}</h4>
                    <ul>
                        <li><strong>{{ translate('messages.pending') }}:</strong> {{ translate('messages.pending_review_description') }}</li>
                        <li><strong>{{ translate('messages.approved') }}:</strong> {{ translate('messages.approved_review_description') }}</li>
                        <li><strong>{{ translate('messages.rejected') }}:</strong> {{ translate('messages.rejected_review_description') }}</li>
                    </ul>

                    <h4 class="mt-4">{{ translate('messages.approving_reviews') }}</h4>
                    <ul>
                        <li>{{ translate('messages.approve_if_review_is_appropriate') }}</li>
                        <li>{{ translate('messages.approve_if_review_follows_guidelines') }}</li>
                        <li>{{ translate('messages.approved_reviews_visible_to_public') }}</li>
                        <li>{{ translate('messages.approved_reviews_affect_salon_rating') }}</li>
                    </ul>

                    <h4 class="mt-4">{{ translate('messages.rejecting_reviews') }}</h4>
                    <ul>
                        <li>{{ translate('messages.reject_if_inappropriate_content') }}</li>
                        <li>{{ translate('messages.reject_if_spam_or_fake') }}</li>
                        <li>{{ translate('messages.reject_if_violates_guidelines') }}</li>
                        <li>{{ translate('messages.add_rejection_reason') }}</li>
                    </ul>

                    <h4 class="mt-4">{{ translate('messages.review_guidelines') }}</h4>
                    <ul>
                        <li>{{ translate('messages.reviews_must_be_related_to_service') }}</li>
                        <li>{{ translate('messages.reviews_must_not_contain_offensive_language') }}</li>
                        <li>{{ translate('messages.reviews_must_not_contain_personal_information') }}</li>
                        <li>{{ translate('messages.reviews_must_be_honest_and_accurate') }}</li>
                    </ul>

                    <h4 class="mt-4">{{ translate('messages.important_notes') }}</h4>
                    <ul>
                        <li>{{ translate('messages.reviews_affect_salon_ratings') }}</li>
                        <li>{{ translate('messages.reviews_affect_salon_ranking') }}</li>
                        <li>{{ translate('messages.rejected_reviews_not_visible') }}</li>
                        <li>{{ translate('messages.customers_notified_of_review_status') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

