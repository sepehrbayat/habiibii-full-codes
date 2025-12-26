<div id="sidebarMain" class="d-none">
    <aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                <!-- Logo -->
                @php
                    $store_logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first();
                    // Fix: Use ->first() method instead of [0] array access to safely handle null storage
                    // رفع: استفاده از متد ->first() به جای دسترسی آرایه [0] برای مدیریت ایمن null storage
                    $storage_value = $store_logo?->storage?->first()?->value ?? 'public';
                @endphp
                <a class="navbar-brand" href="{{ route('admin.beautybooking.dashboard') }}" aria-label="Front">
                       <img class="navbar-brand-logo initial--36 onerror-image onerror-image" data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}"
                    src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value?? '', $storage_value,'favicon')}}"
                    alt="Logo">
                    <img class="navbar-brand-logo-mini initial--36 onerror-image onerror-image" data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}"
                    src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value?? '', $storage_value,'favicon')}}"
                    alt="Logo">
                </a>
                <!-- End Logo -->

                <!-- Navbar Vertical Toggle -->
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                    <i class="tio-clear tio-lg"></i>
                </button>
                <!-- End Navbar Vertical Toggle -->

                <div class="navbar-nav-wrap-content-left">
                    <!-- Navbar Vertical Toggle -->
                    <button type="button" class="js-navbar-vertical-aside-toggle-invoker close">
                        <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                        data-placement="right" title="Collapse"></i>
                        <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                        data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'></i>
                    </button>
                    <!-- End Navbar Vertical Toggle -->
                </div>

            </div>

            <!-- Content -->
            <div class="navbar-vertical-content bg--005555" id="navbar-vertical-content">
                <form autocomplete="off"   class="sidebar--search-form">
                    <div class="search--form-group">
                        <button type="button" class="btn"><i class="tio-search"></i></button>
                        <input  autocomplete="false" name="qq" type="text" class="form-control form--control" placeholder="{{ translate('Search Menu...') }}" id="search">

                        <div id="search-suggestions" class="flex-wrap mt-1"></div>
                    </div>
                </form>

                <ul class="navbar-nav navbar-nav-lg nav-tabs">
                    <!-- Dashboards -->
                    <li class="navbar-vertical-aside-has-menu @yield('dashboard') {{ Request::is('admin/beautybooking') || Request::is('admin/beautybooking/') ? 'show active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.dashboard') }}" title="{{ translate('messages.dashboard') }}">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('messages.dashboard') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Dashboards -->

                    @if (\App\CentralLogics\Helpers::module_permission_check('salon'))
                        <!-- Salon Management -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('Salon Management') }}">{{ translate('Salon Management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/salon*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.salon.list') }}" title="{{ translate('Salons') }}">
                                <i class="tio-store nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('Salons') }}
                                    @php
                                        $pendingSalons = \Modules\BeautyBooking\Entities\BeautySalon::where('verification_status', 0)->count();
                                    @endphp
                                    @if($pendingSalons > 0)
                                        <span class="badge badge-soft-danger badge-pill ml-1">
                                            {{ $pendingSalons }}
                                        </span>
                                    @endif
                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/category*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.category.list') }}" title="{{ translate('Service Categories') }}">
                                <i class="tio-category nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('Service Categories') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::module_permission_check('booking'))
                        <!-- Booking Management -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('Booking Management') }}">{{ translate('Booking Management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/booking*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ translate('Bookings') }}">
                                <i class="tio-calendar-note nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('Bookings') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/beautybooking/booking*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/beautybooking/booking/list') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.booking.list') }}" title="{{ translate('Booking List') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            {{ translate('All Bookings') }}
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{ \Modules\BeautyBooking\Entities\BeautyBooking::count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/booking/calendar') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.booking.calendar') }}" title="{{ translate('Calendar View') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Calendar View') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/staff/calendar*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.staff.calendar') }}" title="{{ translate('messages.staff_calendar') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.staff_calendar') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/refund*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.refund.list') }}" title="{{ translate('messages.refund_requests') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.refund_requests') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/service-relations*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.service-relations.list') }}" title="{{ translate('messages.service_relations') }}">
                                <i class="tio-link nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.service_relations') }}
                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/review*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.review.list') }}" title="{{ translate('Reviews') }}">
                                <i class="tio-star nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('Reviews') }}
                                    @php
                                        $pendingReviews = \Modules\BeautyBooking\Entities\BeautyReview::where('status', 'pending')->count();
                                    @endphp
                                    @if($pendingReviews > 0)
                                        <span class="badge badge-soft-warning badge-pill ml-1">
                                            {{ $pendingReviews }}
                                        </span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::module_permission_check('subscription'))
                        <!-- Subscription & Revenue -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('Subscription & Revenue') }}">{{ translate('Subscription & Revenue') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/subscription*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ translate('Subscriptions') }}">
                                <i class="tio-money nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('Subscriptions') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/beautybooking/subscription*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/beautybooking/subscription/list') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.subscription.list') }}" title="{{ translate('Subscription List') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Subscription List') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/subscription/plans') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.subscription.plans') }}" title="{{ translate('messages.subscription_plans') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.subscription_plans') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/subscription/ads') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.subscription.ads') }}" title="{{ translate('Ads Management') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Ads Management') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/flash-sale*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.flash-sale.list') }}" title="{{ translate('messages.flash_sale') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.flash_sale') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/banner*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ translate('messages.banners') }}">
                                <i class="tio-megaphone nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.promotions') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/beautybooking/banner*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/beautybooking/banner/promotion') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.banner.promotion') }}" title="{{ translate('messages.promotion_banners') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.promotion_banners') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/banner/coupon') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.banner.coupon') }}" title="{{ translate('messages.coupon_banners') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.coupon_banners') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/banner/push-notification') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.banner.push') }}" title="{{ translate('messages.push_notifications') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.push_notifications') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/banner/advertisement') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.banner.advertisement') }}" title="{{ translate('messages.advertisement_banners') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.advertisement_banners') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/commission*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.commission.index') }}" title="{{ translate('Commission Settings') }}">
                                <i class="tio-settings nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('Commission Settings') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::module_permission_check('package'))
                        <!-- Packages & Gift Cards -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('Packages & Gift Cards') }}">{{ translate('Packages & Gift Cards') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/package*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.package.list') }}" title="{{ translate('Packages') }}">
                                <i class="tio-box nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('Packages') }}
                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/gift-card*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.gift-card.list') }}" title="{{ translate('Gift Cards') }}">
                                <i class="tio-gift nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('Gift Cards') }}
                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/store*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.store.list') }}" title="{{ translate('messages.stores') }}">
                                <i class="tio-store nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.stores') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::module_permission_check('retail'))
                        <!-- Retail & Loyalty -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('Retail & Loyalty') }}">{{ translate('Retail & Loyalty') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/retail*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.retail.list') }}" title="{{ translate('Retail Products') }}">
                                <i class="tio-shopping-basket-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('Retail Products') }}
                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/loyalty*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.loyalty.list') }}" title="{{ translate('Loyalty Campaigns') }}">
                                <i class="tio-star nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('Loyalty Campaigns') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::module_permission_check('report'))
                        <!-- Reports -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('Reports') }}">{{ translate('Reports') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/reports*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ translate('Reports') }}">
                                <i class="tio-chart-bar nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('Reports') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/beautybooking/reports*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/beautybooking/reports/financial') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.reports.financial') }}" title="{{ translate('Financial Report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Financial Report') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/reports/monthly-summary') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.reports.monthly-summary') }}" title="{{ translate('Monthly Summary') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Monthly Summary') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/reports/revenue-breakdown') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.reports.revenue-breakdown') }}" title="{{ translate('Revenue Breakdown') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Revenue Breakdown') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/reports/top-rated') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.reports.top-rated') }}" title="{{ translate('Top Rated Salons') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Top Rated Salons') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/reports/trending') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.reports.trending') }}" title="{{ translate('Trending Clinics') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Trending Clinics') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/reports/package-usage') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.reports.package-usage') }}" title="{{ translate('Package Usage') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Package Usage') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/reports/loyalty-stats') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.reports.loyalty-stats') }}" title="{{ translate('Loyalty Statistics') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('Loyalty Statistics') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    <!-- Help Documentation -->
                    <li class="nav-item">
                        <small class="nav-subtitle" title="{{ translate('Help & Documentation') }}">{{ translate('Help & Documentation') }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/help*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.help.index') }}" title="{{ translate('Help') }}">
                            <i class="tio-book nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Help') }}
                            </span>
                        </a>
                    </li>

                <li class="nav-item py-5">

                </li>


                <li class="__sidebar-hs-unfold px-2" id="tourb-9">
                    <div class="hs-unfold w-100">
                        <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper" href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#accountNavbarDropdown",
                                    "type": "css-animation"
                                }'>
                            <div class="cmn--media right-dropdown-icon d-flex align-items-center">
                                <div class="avatar avatar-sm avatar-circle">
                                   <img class="avatar-img onerror-image"
                                    data-onerror-image="{{asset('public/assets/admin/img/160x160/img1.jpg')}}"

                                    src="{{auth('admin')->user()?->toArray()['image_full_url']}}"

                                    alt="Image Description">
                                    <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                </div>
                                <div class="media-body pl-3">
                                    <span class="card-title h5">
                                        {{auth('admin')->user()->f_name}}
                                        {{auth('admin')->user()->l_name}}
                                    </span>
                                    <span class="card-text">{{auth('admin')->user()->email}}</span>
                                </div>
                            </div>
                        </a>

                        <div id="accountNavbarDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account min--240">
                            <div class="dropdown-item-text">
                                <div class="media align-items-center">
                                    <div class="avatar avatar-sm avatar-circle mr-2">
                                        <img class="avatar-img onerror-image"
                                    data-onerror-image="{{asset('public/assets/admin/img/160x160/img1.jpg')}}"

                                    src="{{auth('admin')->user()?->toArray()['image_full_url']}}"

                                    alt="Image Description">
                                    </div>
                                    <div class="media-body">
                                        <span class="card-title h5">{{auth('admin')->user()->f_name}}</span>
                                        <span class="card-text">{{auth('admin')->user()->email}}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item" href="{{route('admin.settings')}}">
                                <span class="text-truncate pr-2" title="Settings">{{translate('messages.settings')}}</span>
                            </a>

                            <div class="dropdown-divider"></div>

                           <a class="dropdown-item log-out" href="javascript:">
                                <span class="text-truncate pr-2" title="Sign out">{{translate('messages.sign_out')}}</span>
                            </a>
                        </div>
                    </div>
                </li>
                </ul>
            </div>
            <!-- End Content -->
        </div>
    </aside>
</div>

<div id="sidebarCompact" class="d-none">

</div>

