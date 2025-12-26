<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                <!-- Logo -->
                @php
                    $logo = \App\Models\BusinessSetting::where(['key'=>'icon'])->first();
                    $logo_storage = $logo?->storage?->first()?->value ?? 'public';
                @endphp
                <a class="navbar-brand" href="{{ route('customer.dashboard') }}" aria-label="Front">
                    <img class="navbar-brand-logo initial--36  onerror-image"
                        data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}"
                        src="{{ \App\CentralLogics\Helpers::get_full_url('business', $logo?->value ?? '', $logo_storage, 'favicon') }}" alt="Logo">
                    <img class="navbar-brand-logo-mini initial--36 onerror-image"
                        data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}"
                        src="{{ \App\CentralLogics\Helpers::get_full_url('business', $logo?->value ?? '', $logo_storage, 'favicon') }}" alt="Logo">
                </a>
                <!-- End Logo -->

                <!-- Navbar Vertical Toggle -->
                <button type="button"
                    class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
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
            <div class="navbar-vertical-content text-capitalize bg--005555" id="navbar-vertical-content">
                <ul class="navbar-nav navbar-nav-lg nav-tabs">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <small class="nav-subtitle">{{ translate('Main') }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('dashboard') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:;" title="{{ translate('Dashboard') }}">
                            <i class="tio-dashboard-vs-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Dashboard') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: {{ Request::is('dashboard') ? 'block' : 'none' }}">
                            <li class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('customer.dashboard') }}" title="{{ translate('My Dashboard') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('My Dashboard') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    @if(addon_published_status('BeautyBooking'))
                    <!-- Beauty Booking Section -->
                    <li class="nav-item">
                        <small class="nav-subtitle">{{ translate('Beauty Booking') }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>
                    
                    <!-- Book Appointment -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('beauty-booking/booking*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:;" title="{{ translate('Book Appointment') }}">
                            <i class="tio-calendar-note nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Book Appointment') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: {{ Request::is('beauty-booking/booking*') ? 'block' : 'none' }}">
                            <li class="nav-item {{ Request::is('beauty-booking/my-bookings') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('beauty-booking.my-bookings.index') }}" title="{{ translate('My Bookings') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('My Bookings') }}</span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('beauty-booking/booking/create*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('beauty-booking.booking.create', 0) }}" title="{{ translate('Create Booking') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Create Booking') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Beauty Services -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('beauty-booking/search*') || Request::is('beauty-booking/salon*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:;" title="{{ translate('Beauty Services') }}">
                            <i class="tio-store nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Beauty Services') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: {{ Request::is('beauty-booking/search*') || Request::is('beauty-booking/salon*') ? 'block' : 'none' }}">
                            <li class="nav-item {{ Request::is('beauty-booking/search') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('beauty-booking.search') }}" title="{{ translate('Search Salons') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Search Salons') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('beauty-booking.search') }}?filter=top_rated" title="{{ translate('Top Rated Salons') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Top Rated Salons') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Packages -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('beauty-booking/packages*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:;" title="{{ translate('Packages') }}">
                            <i class="tio-package nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Packages') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: {{ Request::is('beauty-booking/packages*') ? 'block' : 'none' }}">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('beauty-booking.search') }}" title="{{ translate('My Packages') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('My Packages') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('beauty-booking.search') }}" title="{{ translate('Available Packages') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Available Packages') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Gift Cards -->
                    <li class="nav-item {{ Request::is('beauty-booking/gift-cards*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('beauty-booking.gift-cards') }}" title="{{ translate('Gift Cards') }}">
                            <i class="tio-gift nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Gift Cards') }}</span>
                        </a>
                    </li>

                    <!-- Loyalty Program -->
                    <li class="nav-item {{ Request::is('beauty-booking/loyalty*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('beauty-booking.loyalty') }}" title="{{ translate('Loyalty Program') }}">
                            <i class="tio-star nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Loyalty Program') }}</span>
                        </a>
                    </li>

                    <!-- Consultations -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('beauty-booking/consultations*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:;" title="{{ translate('Consultations') }}">
                            <i class="tio-chat nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Consultations') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: {{ Request::is('beauty-booking/consultations*') ? 'block' : 'none' }}">
                            <li class="nav-item {{ Request::is('beauty-booking/consultations') && !request()->has('status') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('beauty-booking.consultations') }}" title="{{ translate('Active Consultations') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Active Consultations') }}</span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('beauty-booking/consultations') && request()->get('status') == 'completed' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('beauty-booking.consultations') }}?status=completed" title="{{ translate('Consultation History') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Consultation History') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Reviews -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('beauty-booking/reviews*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:;" title="{{ translate('Reviews') }}">
                            <i class="tio-comment nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Reviews') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: {{ Request::is('beauty-booking/reviews*') ? 'block' : 'none' }}">
                            <li class="nav-item {{ Request::is('beauty-booking/reviews') && !request()->has('status') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('beauty-booking.reviews') }}" title="{{ translate('My Reviews') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('My Reviews') }}</span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('beauty-booking/reviews') && request()->get('status') == 'pending' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('beauty-booking.reviews') }}?status=pending" title="{{ translate('Pending Reviews') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Pending Reviews') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Retail Shop -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('beauty-booking/retail*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:;" title="{{ translate('Retail Shop') }}">
                            <i class="tio-shopping-cart nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('Retail Shop') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: {{ Request::is('beauty-booking/retail*') ? 'block' : 'none' }}">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('beauty-booking.search') }}" title="{{ translate('Browse Products') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Browse Products') }}</span>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('beauty-booking/retail-orders*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('beauty-booking.retail-orders') }}" title="{{ translate('My Orders') }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('My Orders') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                </ul>
            </div>
            <!-- End Content -->
        </div>
    </aside>
</div>

