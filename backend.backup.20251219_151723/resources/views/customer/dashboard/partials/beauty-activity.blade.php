@if(isset($beautyActivity) && $beautyActivity->count() > 0)
<!-- Recent Activity Feed -->
<!-- فید فعالیت‌های اخیر -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ translate('Recent Activity') }}</h5>
        <button type="button" class="btn btn-sm btn-link" onclick="refreshActivityFeed()">
            <i class="tio-refresh"></i> {{ translate('Refresh') }}
        </button>
    </div>
    <div class="card-body">
        <div id="activity-feed-container">
            <div class="list-group list-group-flush">
                @foreach($beautyActivity as $activity)
                    <div class="list-group-item px-0">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                @if($activity['type'] == 'booking')
                                    <i class="tio-calendar-note fs-2 text-primary"></i>
                                @elseif($activity['type'] == 'review')
                                    <i class="tio-comment fs-2 text-info"></i>
                                @elseif($activity['type'] == 'gift_card')
                                    <i class="tio-gift fs-2 text-danger"></i>
                                @elseif($activity['type'] == 'retail_order')
                                    <i class="tio-shopping-cart fs-2 text-success"></i>
                                @elseif($activity['type'] == 'loyalty_point')
                                    <i class="tio-star fs-2 text-warning"></i>
                                @else
                                    <i class="tio-notifications fs-2 text-secondary"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ $activity['title'] }}</h6>
                                <p class="mb-1 text-muted">{{ $activity['description'] }}</p>
                                <small class="text-muted">
                                    <i class="tio-time"></i> {{ $activity['date']->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('script_2')
<script>
    function refreshActivityFeed() {
        const container = document.getElementById('activity-feed-container');
        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">{{ translate('Loading...') }}</span></div></div>';
        
        fetch('{{ route('customer.dashboard.beauty.activity') }}')
            .then(response => response.json())
            .then(data => {
                if (data.activity && data.activity.length > 0) {
                    let html = '<div class="list-group list-group-flush">';
                    data.activity.forEach(function(activity) {
                        const iconMap = {
                            'booking': 'tio-calendar-note text-primary',
                            'review': 'tio-comment text-info',
                            'gift_card': 'tio-gift text-danger',
                            'retail_order': 'tio-shopping-cart text-success',
                            'loyalty_point': 'tio-star text-warning'
                        };
                        const icon = iconMap[activity.type] || 'tio-notifications text-secondary';
                        const date = new Date(activity.date);
                        html += `
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="${icon} fs-2"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">${activity.title}</h6>
                                        <p class="mb-1 text-muted">${activity.description}</p>
                                        <small class="text-muted">
                                            <i class="tio-time"></i> ${date.toLocaleDateString()}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="text-center py-4"><p class="text-muted">{{ translate('No recent activity') }}</p></div>';
                }
            })
            .catch(error => {
                console.error('Error refreshing activity feed:', error);
                container.innerHTML = '<div class="alert alert-danger">{{ translate('Failed to load activity') }}</div>';
            });
    }
</script>
@endpush
@else
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ translate('Recent Activity') }}</h5>
    </div>
    <div class="card-body">
        <div class="text-center py-4">
            <i class="tio-notifications fs-1 text-muted"></i>
            <p class="text-muted mt-2">{{ translate('No recent activity') }}</p>
        </div>
    </div>
</div>
@endif

