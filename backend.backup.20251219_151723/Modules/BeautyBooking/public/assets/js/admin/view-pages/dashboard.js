"use strict";

/**
 * Initialize Donut Chart for Booking Status
 * مقداردهی اولیه نمودار دونات برای وضعیت رزرو
 *
 * @param {number} pendingCount
 * @param {number} confirmedCount
 * @param {number} completedCount
 * @param {number} cancelledCount
 * @param {number} totalCount
 */
function initializeDonutChart(pendingCount, confirmedCount, completedCount, cancelledCount, totalCount) {
    let options;
    let chart;

    options = {
        series: [pendingCount, confirmedCount, completedCount, cancelledCount],
        chart: {
            width: 320,
            type: 'donut',
        },
        labels: [translate('Pending') || 'Pending', translate('Confirmed') || 'Confirmed', translate('Completed') || 'Completed', translate('Cancelled') || 'Cancelled'],
        dataLabels: {
            enabled: false,
        },
        responsive: [{
            breakpoint: 1650,
            options: {
                chart: {
                    width: 250
                },
            }
        }],
        colors: ['#ffc107', '#17a2b8', '#28a745', '#dc3545'],
        fill: {
            colors: ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
        },
        legend: {
            show: false
        },
    };

    if (chart) {
        chart.destroy();
    }

    chart = new ApexCharts(document.querySelector("#dognut-pie"), options);
    chart.render();
}

/**
 * Initialize Area Chart for Revenue
 * مقداردهی اولیه نمودار ناحیه‌ای برای درآمد
 *
 * @param {Array} totalSell
 * @param {Array} commission
 * @param {Array} totalSubs
 * @param {Array} labels
 */
function initializeAreaChart(totalSell, commission, totalSubs, labels) {
    let ApexChart;
    const options = {
        series: [{
            name: 'Gross Earning',
            data: totalSell
        }, {
            name: 'Commission Earning',
            data: commission
        }, {
            name: 'Subscription Earning',
            data: totalSubs
        }],
        chart: {
            height: 350,
            type: 'area',
            toolbar: {
                show: false
            },
            colors: ['#76ffcd','#ff6d6d', '#005555'],
        },
        dataLabels: {
            enabled: false,
            colors: ['#76ffcd','#ff6d6d', '#005555'],
        },
        stroke: {
            curve: 'smooth',
            width: 2,
            colors: ['#76ffcd','#ff6d6d', '#005555'],
        },
        fill: {
            type: 'gradient',
            colors: ['#76ffcd','#ff6d6d', '#005555'],
        },
        xaxis: {
            categories: labels
        },
        tooltip: {
            x: {
                format: 'dd/MM/yy HH:mm'
            },
        },
    };

    if (ApexChart) {
        ApexChart.destroy();
    }

    ApexChart = new ApexCharts(document.querySelector("#grow-sale-chart"), options);
    ApexChart.render();
}

// Zone filter change handler
// مدیریت تغییر فیلتر منطقه
$('.fetch_data_zone_wise').on('change', function () {
    let zone_id = $('.fetch_data_zone_wise').val();
    fetch_data_zone_wise(zone_id);
});

/**
 * Fetch dashboard data based on zone
 * دریافت داده‌های داشبورد بر اساس منطقه
 *
 * @param {string} zone_id
 */
function fetch_data_zone_wise(zone_id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.get({
        url: $('.fetch_data_zone_wise').data('src-url'),
        data: {
            zone_id: zone_id
        },
        beforeSend: function() {
            $('#loading').show();
            $('#loading-commission').show();
            $('#loading-booking-overview').show();
        },
        success: function(data) {
            $('#bookingStatistics').html(data.booking_statistics);
            $('#commission-overview-board').html(data.sale_chart)
            $('#topSalons').html(data.top_salons)
            $('#topCustomers').html(data.top_customers)
            $('#booking-overview-board').html(data.by_booking_status)
            $('#zoneName').html(data.zoneName);
            $('.gross-earning').text(formatCurrency(data.grossEarning));
            initializeDonutChart(data.pendingCount, data.confirmedCount, data.completedCount, data.cancelledCount, data.totalCount);
            initializeAreaChart(data.total_sell, data.commission, data.total_subs, data.labels);
        },
        error: function(xhr, status, error) {
            if (window.BeautyErrorHandler && window.BeautyErrorHandler.showAjaxError) {
                window.BeautyErrorHandler.showAjaxError(xhr, status, error);
            } else {
                console.error('Error fetching zone data:', error);
            }
        },
        complete: function() {
            $('#loading').hide();
            $('#loading-commission').hide();
            $('#loading-booking-overview').hide();
        }
    });
}

// Statistics type filter change handler
// مدیریت تغییر فیلتر نوع آمار
$('.booking_stats_update').on('change', function () {
    let zone_id = $('.fetch_data_zone_wise').val();
    let statistics_type = $('.booking_stats_update').val();

    booking_stats_update(zone_id, statistics_type);
});

/**
 * Update booking statistics
 * به‌روزرسانی آمار رزرو
 *
 * @param {string} zone_id
 * @param {string} statistics_type
 */
function booking_stats_update(zone_id, statistics_type) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.get({
        url: $('.fetch_data_zone_wise').data('src-url'),
        data: {
            zone_id: zone_id,
            statistics_type: statistics_type
        },
        beforeSend: function() {
            $('#loading').show();
        },
        success: function(data) {
            $('#bookingStatistics').html(data.booking_statistics);
            $('#zoneName').html(data.zoneName);
            initializeDonutChart(data.pendingCount, data.confirmedCount, data.completedCount, data.cancelledCount, data.totalCount);
        },
        error: function(xhr, status, error) {
            if (window.BeautyErrorHandler && window.BeautyErrorHandler.showAjaxError) {
                window.BeautyErrorHandler.showAjaxError(xhr, status, error);
            } else {
                console.error('Error updating booking statistics:', error);
            }
        },
        complete: function() {
            $('#loading').hide();
        }
    });
}

// Booking overview filter change handler
// مدیریت تغییر فیلتر نمای کلی رزرو
$('.booking_overview_stats_update').on('change', function() {
    let type = $(this).val();
    let zone_id = $('.fetch_data_zone_wise').val();
    booking_overview_stats_update(type, zone_id);
});

/**
 * Update booking overview statistics
 * به‌روزرسانی آمار نمای کلی رزرو
 *
 * @param {string} type
 * @param {string} zone_id
 */
function booking_overview_stats_update(type, zone_id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.get({
        url: $('#booking_by_status_stats_update').data('src-url'),
        data: {
            booking_overview: type,
            zone_id: zone_id
        },
        beforeSend: function() {
            $('#loading-booking-overview').show();
        },
        success: function(data) {
            insert_param('booking_overview', type);
            $('#booking-overview-board').html(data.view);
            const pendingCount = data.pendingCount;
            const confirmedCount = data.confirmedCount;
            const completedCount = data.completedCount;
            const cancelledCount = data.cancelledCount;
            const totalCount = data.totalCount;
            initializeDonutChart(pendingCount, confirmedCount, completedCount, cancelledCount, totalCount);
        },
        error: function(xhr, status, error) {
            if (window.BeautyErrorHandler && window.BeautyErrorHandler.showAjaxError) {
                window.BeautyErrorHandler.showAjaxError(xhr, status, error);
            } else {
                console.error('Error updating booking overview:', error);
            }
        },
        complete: function() {
            $('#loading-booking-overview').hide();
        }
    });
}

// Commission overview filter change handler
// مدیریت تغییر فیلتر نمای کلی کمیسیون
$('.commission_overview_stats_update').on('change', function() {
    let type = $(this).val();
    let zone_id = $('.fetch_data_zone_wise').val();
    commission_overview_stats_update(type, zone_id);
});

/**
 * Update commission overview chart
 * به‌روزرسانی نمودار نمای کلی کمیسیون
 *
 * @param {string} type
 * @param {string} zone_id
 */
function commission_overview_stats_update(type, zone_id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.get({
        url: $('#commission_overview_stats_update').data('src-url'),
        data: {
            commission_overview: type,
            zone_id: zone_id
        },
        beforeSend: function() {
            $('#loading-commission').show();
        },
        success: function(data) {
            let grossEarningTotal = (data.grossEarning).toFixed(2)
            insert_param('commission_overview', type);
            $('#commission-overview-board').html(data.view);
            $('.gross-earning').text(formatCurrency(grossEarningTotal));
            initializeAreaChart(data.total_sell, data.commission, data.total_subs, data.labels);
        },
        error: function(xhr, status, error) {
            if (window.BeautyErrorHandler && window.BeautyErrorHandler.showAjaxError) {
                window.BeautyErrorHandler.showAjaxError(xhr, status, error);
            } else {
                console.error('Error updating commission overview:', error);
            }
        },
        complete: function() {
            $('#loading-commission').hide();
        }
    });
}

const currencySymbol = document.getElementById('current_currency').dataset.currency.trim();

/**
 * Format currency value
 * فرمت کردن مقدار ارز
 *
 * @param {string|number} value
 * @return {string}
 */
function formatCurrency(value) {
    const formattedValue = Number(value).toFixed(2);
    return `${currencySymbol}${formattedValue}`;
}

const currentUrl = document.getElementById('current_url').dataset.srcUrl.trim();

/**
 * Insert or update URL parameter
 * افزودن یا به‌روزرسانی پارامتر URL
 *
 * @param {string} key
 * @param {string} value
 */
function insert_param(key, value) {
    key = encodeURIComponent(key);
    value = encodeURIComponent(value);
    let kvp = document.location.search.substr(1).split('&');
    let i = 0;

    for (; i < kvp.length; i++) {
        if (kvp[i].startsWith(key + '=')) {
            let pair = kvp[i].split('=');
            pair[1] = value;
            kvp[i] = pair.join('=');
            break;
        }
    }
    if (i >= kvp.length) {
        kvp[kvp.length] = [key, value].join('=');
    }

    const params = kvp.join('&');
    const newUrl = params ? `${currentUrl}?${params}` : currentUrl;
    window.history.pushState('page2', 'Title', newUrl);
}
