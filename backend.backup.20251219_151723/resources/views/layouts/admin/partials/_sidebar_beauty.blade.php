<div id="sidebarMain" class="d-none">
    <aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                <!-- Logo -->
                @php($store_logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first())
                <a class="navbar-brand" href="{{ route('admin.dashboard') }}" aria-label="Front">
                    <img class="navbar-brand-logo initial--36 onerror-image onerror-image" data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                        src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value?? '', $store_logo?->storage[0]?->value ?? 'public','favicon')}}"
                        alt="Logo">
                    <img class="navbar-brand-logo-mini initial--36 onerror-image onerror-image" data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                        src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value?? '', $store_logo?->storage[0]?->value ?? 'public','favicon')}}"
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
                <form autocomplete="off" class="sidebar--search-form">
                    <div class="search--form-group">
                        <button type="button" class="btn"><i class="tio-search"></i></button>
                        <input autocomplete="false" name="qq" type="text" class="form-control form--control" placeholder="{{ translate('Search Menu...') }}" id="search">
                        <div id="search-suggestions" class="flex-wrap mt-1"></div>
                    </div>
                </form>

                <ul class="navbar-nav navbar-nav-lg nav-tabs">
                    <!-- Dashboard -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking') || Request::is('admin/beautybooking/dashboard*') ? 'show active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.dashboard') }}" title="{{ translate('messages.beauty_dashboard') }}">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('messages.beauty_dashboard') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Dashboard -->

                    @if (config('beautybooking.features.salon.enabled', true) && \App\CentralLogics\Helpers::module_permission_check('beauty_salon'))
                        <!-- Salon Management -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('messages.beauty_salon_management') }}">{{ translate('messages.beauty_salon_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/salon*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.salon.list') }}" title="{{ translate('messages.beauty_salons') }}">
                                <i class="tio-store nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.beauty_salons') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (config('beautybooking.features.salon.enabled', true) && \App\CentralLogics\Helpers::module_permission_check('beauty_category'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/category*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.category.list') }}" title="{{ translate('messages.beauty_categories') }}">
                                <i class="tio-category nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.beauty_categories') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (config('beautybooking.features.booking.enabled', true) && \App\CentralLogics\Helpers::module_permission_check('beauty_booking'))
                        <!-- Booking Management -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('messages.beauty_booking_management') }}">{{ translate('messages.beauty_booking_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/booking*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ translate('messages.beauty_bookings') }}">
                                <i class="tio-calendar nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.beauty_bookings') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/beautybooking/booking*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/beautybooking/booking/list*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.booking.list') }}" title="{{ translate('messages.all_bookings') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.all_bookings') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/booking/calendar*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.booking.calendar') }}" title="{{ translate('messages.beauty_calendar') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.beauty_calendar') }}</span>
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
                                <li class="nav-item {{ Request::is('admin/beautybooking/flash-sale*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.flash-sale.list') }}" title="{{ translate('messages.flash_sale') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.flash_sale') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/banner*') ? 'active' : '' }}">
                                    <a class="nav-link nav-link-toggle" href="javascript:" title="{{ translate('messages.banners') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.promotions') }}</span>
                                    </a>
                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/beautybooking/banner*') ? 'block' : 'none' }}">
                                        <li class="nav-item {{ Request::is('admin/beautybooking/banner/promotion') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.beautybooking.banner.promotion') }}">{{ translate('messages.promotion_banners') }}</a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/beautybooking/banner/coupon') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.beautybooking.banner.coupon') }}">{{ translate('messages.coupon_banners') }}</a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/beautybooking/banner/push-notification') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.beautybooking.banner.push') }}">{{ translate('messages.push_notifications') }}</a>
                                        </li>
                                        <li class="nav-item {{ Request::is('admin/beautybooking/banner/advertisement') ? 'active' : '' }}">
                                            <a class="nav-link" href="{{ route('admin.beautybooking.banner.advertisement') }}">{{ translate('messages.advertisement_banners') }}</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/service-relations*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.service-relations.list') }}" title="{{ translate('messages.service_relations') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.service_relations') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::module_permission_check('beauty_review'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/review*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.review.list') }}" title="{{ translate('messages.beauty_reviews') }}">
                                <i class="tio-star nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.beauty_reviews') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (config('beautybooking.features.booking.enabled', true) && \App\CentralLogics\Helpers::module_permission_check('beauty_package'))
                        <!-- Package & Gift Card Management -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('messages.beauty_packages_gift_cards') }}">{{ translate('messages.beauty_packages_gift_cards') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/package*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.package.list') }}" title="{{ translate('messages.beauty_packages') }}">
                                <i class="tio-box nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.beauty_packages') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (config('beautybooking.features.booking.enabled', true) && \App\CentralLogics\Helpers::module_permission_check('beauty_gift_card'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/gift-card*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.gift-card.list') }}" title="{{ translate('messages.beauty_gift_cards') }}">
                                <i class="tio-gift nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.beauty_gift_cards') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (config('beautybooking.features.retail.enabled', true) && \App\CentralLogics\Helpers::module_permission_check('beauty_retail'))
                        <!-- Retail Management -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('messages.beauty_retail_management') }}">{{ translate('messages.beauty_retail_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/retail*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.retail.list') }}" title="{{ translate('messages.beauty_retail_products') }}">
                                <i class="tio-shopping-basket nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.beauty_retail_products') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (config('beautybooking.features.loyalty.enabled', true) && \App\CentralLogics\Helpers::module_permission_check('beauty_loyalty'))
                        <!-- Loyalty Management -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('messages.beauty_loyalty_management') }}">{{ translate('messages.beauty_loyalty_management') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/loyalty*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.loyalty.list') }}" title="{{ translate('messages.beauty_loyalty_campaigns') }}">
                                <i class="tio-medal nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.beauty_loyalty_campaigns') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (config('beautybooking.features.subscription.enabled', true) && \App\CentralLogics\Helpers::module_permission_check('beauty_subscription'))
                        <!-- Subscription & Ads Management -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('messages.beauty_subscription_ads') }}">{{ translate('messages.beauty_subscription_ads') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/subscription*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ translate('messages.beauty_subscriptions') }}">
                                <i class="tio-card nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.beauty_subscriptions') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/beautybooking/subscription*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/beautybooking/subscription/list*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.subscription.list') }}" title="{{ translate('messages.active_subscriptions') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.active_subscriptions') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/subscription/plans*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.subscription.plans') }}" title="{{ translate('messages.subscription_plans') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.subscription_plans') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/subscription/ads*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.subscription.ads') }}" title="{{ translate('messages.banner_ads') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.banner_ads') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if (\App\CentralLogics\Helpers::module_permission_check('beauty_commission'))
                        <!-- Commission & Settings -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('messages.beauty_settings') }}">{{ translate('messages.beauty_settings') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/commission*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.beautybooking.commission.index') }}" title="{{ translate('messages.beauty_commission_settings') }}">
                                <i class="tio-settings nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.beauty_commission_settings') }}
                                </span>
                            </a>
                        </li>
                    @endif

                    @if (config('beautybooking.features.reports.enabled', true) && \App\CentralLogics\Helpers::module_permission_check('beauty_report'))
                        <!-- Reports -->
                        <li class="nav-item">
                            <small class="nav-subtitle" title="{{ translate('messages.beauty_reports') }}">{{ translate('messages.beauty_reports') }}</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/beautybooking/reports*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="{{ translate('messages.beauty_reports') }}">
                                <i class="tio-chart-bar-4 nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{ translate('messages.beauty_reports') }}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/beautybooking/reports*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/beautybooking/reports/financial*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.reports.financial') }}" title="{{ translate('messages.financial_report') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.financial_report') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/reports/revenue-breakdown*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.reports.revenue-breakdown') }}" title="{{ translate('messages.revenue_breakdown') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.revenue_breakdown') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/reports/top-rated*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.reports.top-rated') }}" title="{{ translate('messages.top_rated_salons') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.top_rated_salons') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/beautybooking/reports/trending*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.beautybooking.reports.trending') }}" title="{{ translate('messages.trending_clinics') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{ translate('messages.trending_clinics') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </aside>
</div>
