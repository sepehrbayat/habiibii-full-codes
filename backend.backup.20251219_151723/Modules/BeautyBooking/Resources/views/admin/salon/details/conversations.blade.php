<!-- Conversations Tab Content -->
<!-- محتوای تب مکالمات -->
<div class="row pt-2">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">{{ translate('messages.conversation_list') }}</h1>
        </div>
        <!-- End Page Header -->

        <div class="row g-3">
            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card">
                    <div class="card-header border-0">
                        <div class="input-group input---group">
                            <div class="input-group-prepend border-inline-end-0">
                                <span class="input-group-text border-inline-end-0" id="basic-addon1"><i class="tio-search"></i></span>
                            </div>
                            <input type="text" class="form-control border-inline-start-0 pl-1" id="serach" placeholder="{{translate('messages.Search')}}" aria-label="Username"
                                aria-describedby="basic-addon1" autocomplete="off">
                        </div>
                    </div>
                    <input type="hidden" id="vendor_id" value="{{ $salon->store->id ?? 0 }}">
                    <!-- Body -->
                    <div class="card-body p-0 conversation-list-scroll" id="vendor-conversation-list">
                        <div class="border-bottom"></div>
                        @include('admin-views.vendor.view.partials._conversation_list')
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
            <div class="col-lg-8 col-nd-6" id="vendor-view-conversation">
                <div class="text-center mt-2">
                    <h4 class="initial-29">{{ translate('messages.view_conversation') }}
                    </h4>
                </div>
            </div>
        </div>
        <!-- End Row -->
    </div>
</div>

<div class="d-none" id="data-set"
     data-message-list-url="{{ route('admin.store.message-list') }}"
     data-view-conv-url="{{ route('admin.beautybooking.salon.view', ['id' => $salon->id, 'tab' => 'conversations']) }}"
>
</div>