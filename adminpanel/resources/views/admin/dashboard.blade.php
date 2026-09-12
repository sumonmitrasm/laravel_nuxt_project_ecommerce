@extends('admin.layout.layout')
@section('content')
    <div class="app-content main-content">
        <div class="side-app">
            <div class="container-fluid main-container">
                <div class="page-header">
                    <div class="page-leftheader">
                        <h4 class="page-title">Commerce Dashboard</h4>
                        <p class="text-muted mb-0">Live store performance • {{ $periodLabel }}</p>
                    </div>
                    <div class="page-rightheader"><a href="{{ route('admin-orders.analytics') }}" data-ajax-page
                            class="btn btn-primary">View sales analytics</a></div>
                </div>
                <div class="card dashboard-filter">
                    <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div><b>Dashboard period:</b> <span class="text-muted">{{ $periodLabel }}</span></div>
                        <div class="btn-group flex-wrap">
                            @foreach (['today' => 'Today', '7_days' => 'Last 7 days', 'month' => 'This month', 'year' => 'This year', 'all' => 'All time'] as $key => $label)
                                <a data-ajax-page href="{{ route('admin.dashboard', ['period' => $key]) }}"
                                    class="btn btn-sm {{ $period === $key ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @php
                    $statusColors = [
                        'pending' => 'warning',
                        'confirmed' => 'primary',
                        'processing' => 'info',
                        'shipped' => 'secondary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    ];
                    $chartMax = max(1, (float) $revenueChart->max('revenue'));
                @endphp
                <div class="row">
                    @foreach ([['Today sales', $todaySales, '&#2547;', 'success', 'si-wallet'], [$periodLabel . ' sales', $periodSales, '&#2547;', 'primary', 'si-graph'], ['Pending orders', (int) ($statusCounts['pending'] ?? 0), '', 'warning', 'si-hourglass'], ['Processing orders', (int) ($statusCounts['processing'] ?? 0), '', 'info', 'si-settings'], ['Shipped orders', (int) ($statusCounts['shipped'] ?? 0), '', 'secondary', 'si-plane'], ['Low-stock variants', $lowStockCount, '', 'danger', 'si-exclamation'], ['New customers (' . $periodLabel . ')', $newCustomers, '', 'success', 'si-user-follow']] as [$label, $value, $symbol, $color, $icon])
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card dash-stat">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="text-muted mb-2">{{ $label }}</p>
                                            <h3 class="mb-0 text-{{ $color }}">
                                                {!! $symbol !!}{{ $symbol ? number_format((float) $value, 2) : number_format((int) $value) }}
                                            </h3>
                                        </div><span class="dash-icon text-{{ $color }}"><i
                                                class="si {{ $icon }}"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card dash-stat dashboard-welcome">
                            <div class="card-body">
                                <p class="mb-2">Signed in as</p>
                                <h4 class="mb-1">{{ Auth::guard('admin')->user()?->name }}</h4>
                                <small>{{ ucfirst((string) Auth::guard('admin')->user()?->type) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card revenue-card">
                            <div class="card-header revenue-heading">
                                <div>
                                    <h3 class="card-title mb-1">Revenue trend — {{ $periodLabel }}</h3><small
                                        class="text-muted">প্রতিটি point ওই সময়ের paid online এবং delivered COD sales
                                        দেখায়</small>
                                </div>
                                <div class="revenue-legend"><span><i></i> Recognized revenue</span></div>
                            </div>
                            <div class="card-body">
                                <div class="revenue-summary">
                                    <div><small>Total revenue</small><b>&#2547;{{ number_format($periodSales, 2) }}</b>
                                    </div>
                                    <div><small>Successful orders</small><b>{{ number_format($periodOrderCount) }}</b>
                                    </div>
                                    <div><small>Average order</small><b>&#2547;{{ number_format($periodAverage, 2) }}</b>
                                    </div>
                                </div>
                                <div class="revenue-canvas"><canvas id="dashboard-revenue-chart"></canvas></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header d-block">
                                <h3 class="card-title mb-1">Top-selling categories</h3><small
                                    class="text-muted d-block">{{ $periodLabel }}</small>
                            </div>
                            <div class="card-body">
                                @forelse($topCategories as $category)
                                    <div class="rank-row"><span class="rank">{{ $loop->iteration }}</span>
                                        <div class="flex-grow-1"><b>{{ $category->category_name }}</b><small
                                                class="d-block text-muted">{{ number_format($category->units_sold) }} units
                                                sold</small></div>
                                        <strong>&#2547;{{ number_format((float) $category->product_sales, 2) }}</strong>
                                </div>@empty<div class="empty-state">No completed category sales this month.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Recent orders</h3><a href="{{ route('admin-orders.index') }}"
                                    data-ajax-page>View all</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">Order</th>
                                                <th>Customer</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Items</th>
                                                <th class="text-end pe-4">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentOrders as $order)
                                                <tr>
                                                    <td class="ps-4"><a data-ajax-page
                                                            href="{{ route('admin-orders.show', $order) }}"
                                                            class="fw-semibold">{{ $order->order_number }}</a></td>
                                                    <td>{{ $order->user?->name ?: 'Customer' }}<small
                                                            class="d-block text-muted">{{ $order->user?->email }}</small>
                                                    </td>
                                                    <td>{{ optional($order->placed_at)->format('d M Y') }}<small
                                                            class="d-block text-muted">{{ optional($order->placed_at)->format('h:i A') }}</small>
                                                    </td>
                                                    <td><span
                                                            class="badge bg-{{ $statusColors[$order->order_status] ?? 'secondary' }}">{{ ucfirst($order->order_status) }}</span>
                                                    </td>
                                                    <td>{{ $order->items_count }}</td>
                                                    <td class="text-end pe-4">
                                                        &#2547;{{ number_format((float) $order->grand_total, 2) }}</td>
                                            </tr>@empty<tr>
                                                    <td colspan="6" class="empty-state">No orders yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div>
                                    <h3 class="card-title">Low-stock products</h3><small class="text-muted">Based on each
                                        variant threshold</small>
                                </div><span class="badge bg-danger">{{ $lowStockCount }}</span>
                            </div>
                            <div class="card-body">
                                @forelse($lowStockProducts as $variant)
                                    <div class="stock-row">
                                        <div class="stock-thumb">
                                            @if ($variant->product?->product_image)
                                                <img src="{{ asset('admin/productimage/' . basename($variant->product->product_image)) }}"
                                                alt="">@else<span>—</span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <b>{{ $variant->product?->product_name ?: 'Deleted product' }}</b><small
                                                class="d-block text-muted">SKU: {{ $variant->sku ?: 'N/A' }}</small></div>
                                        <span
                                            class="badge {{ $variant->stock === 0 ? 'bg-danger' : 'bg-warning' }}">{{ $variant->stock }}
                                            left</span>
                                </div>@empty<div class="empty-state">All active variants have healthy stock.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .dash-stat {
            min-height: 122px
        }

        .dash-icon {
            font-size: 27px;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(98, 105, 220, .09);
            display: grid;
            place-items: center
        }

        .dashboard-welcome {
            color: #fff;
            background: linear-gradient(135deg, #123c2e, #237354)
        }

        .revenue-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px
        }

        .revenue-legend span {
            font-size: 12px;
            color: #9ca8bf;
            white-space: nowrap
        }

        .revenue-legend i {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #39c88a;
            margin-right: 6px
        }

        .revenue-summary {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px
        }

        .revenue-summary div {
            min-width: 145px;
            padding: 10px 14px;
            border: 1px solid rgba(130, 140, 160, .18);
            border-radius: 9px
        }

        .revenue-summary small {
            display: block;
            color: #8d99b2;
            margin-bottom: 3px
        }

        .revenue-summary b {
            font-size: 16px
        }

        .revenue-canvas {
            position: relative;
            height: 270px;
            max-width: 100%
        }

        .rank-row,
        .stock-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(130, 140, 160, .17)
        }

        .rank-row:last-child,
        .stock-row:last-child {
            border: 0
        }

        .rank {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            background: rgba(74, 90, 210, .12);
            color: #7485ff;
            font-weight: 700
        }

        .stock-thumb {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .06);
            display: grid;
            place-items: center;
            overflow: hidden
        }

        .stock-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain
        }

        .empty-state {
            text-align: center;
            color: #8b96aa;
            padding: 35px 10px
        }

        @media(max-width:767px) {
            .revenue-heading {
                align-items: flex-start;
                flex-direction: column
            }

            .revenue-canvas {
                height: 240px
            }

            .revenue-summary div {
                min-width: calc(50% - 6px);
                flex: 1
            }
        }
    </style>
    <script>
        (function() {
            var attempts = 0;

            function drawRevenueChart() {
                var canvas = document.getElementById('dashboard-revenue-chart');
                if (!canvas) return;
                if (typeof Chart === 'undefined') {
                    if (attempts++ < 30) setTimeout(drawRevenueChart, 100);
                    return;
                }
                var labels = @json($revenueChart->pluck('label')->values());
                var values = @json($revenueChart->pluck('revenue')->map(fn($value) => (float) $value)->values());
                new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Recognized revenue',
                            data: values,
                            borderColor: '#3bc98b',
                            backgroundColor: 'rgba(59,201,139,.14)',
                            pointBackgroundColor: '#3bc98b',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            borderWidth: 3,
                            lineTension: .35,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false
                        },
                        tooltips: {
                            mode: 'index',
                            intersect: false,
                            displayColors: false,
                            callbacks: {
                                label: function(item) {
                                    return 'Revenue: ৳' + Number(item.yLabel || 0).toLocaleString('en-BD', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        },
                        hover: {
                            mode: 'nearest',
                            intersect: false
                        },
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false
                                },
                                ticks: {
                                    fontColor: '#8e9ab2',
                                    maxRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 12
                                }
                            }],
                            yAxes: [{
                                gridLines: {
                                    color: 'rgba(140,150,175,.13)',
                                    drawBorder: false
                                },
                                ticks: {
                                    beginAtZero: true,
                                    fontColor: '#8e9ab2',
                                    callback: function(value) {
                                        if (value >= 1000000) return '৳' + (value / 1000000)
                                            .toFixed(1) + 'M';
                                        if (value >= 1000) return '৳' + (value / 1000).toFixed(0) +
                                            'K';
                                        return '৳' + value;
                                    }
                                }
                            }]
                        }
                    }
                });
            }
            drawRevenueChart();
        })();
    </script>
@endsection
