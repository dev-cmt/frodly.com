@extends('backEnd.layouts.master')

@section('title', 'Dashboard')
@push('css')
<style>
    .logo_brand {
        font-size: 44px;
        font-weight: bold;
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(270deg, #f1f1f1, #00fff2, #00f128);
        background-size: 600% 600%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: gradientMove 4s ease infinite;
        text-transform: uppercase;
        letter-spacing: 4px;
        text-align: center;
    }
    @keyframes gradientMove {
        0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}
    }
</style>
@endpush

@php
use Illuminate\Support\Facades\DB;

$totalClients = DB::table('users')->where('is_admin', 0)->count();
$activePackageCount = DB::table('pricing_plans')->where('status', 1)->count();
$totalRequests = DB::table('sales')->sum('requests_count');
$newLeads = DB::table('users')
    ->where('is_admin', 0)
    ->whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->count();
@endphp


@section('content')
@if (Auth::user()->is_admin)
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-semibold fs-18 mb-0">
        Welcome {{ Auth::user()->name }}
        {{-- ({{ ucfirst(Auth::user()->getRoleNames()->first()) }} Dashboard) --}}
    </h1>
    <nav>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        </ol>
    </nav>
</div>

<div class="row">

    {{-- Total Requests --}}
    <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-6">
        <div class="card custom-card hrm-main-card primary">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar bg-primary">
                            <i class="ri-service-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="fw-semibold text-muted d-block mb-2">Total Requests</span>
                        <h5 class="fw-semibold mb-2">{{ number_format($totalRequests) }}</h5>
                        <span class="badge bg-primary-transparent">All Time</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Clients --}}
    <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-6">
        <div class="card custom-card hrm-main-card secondary">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar bg-secondary">
                            <i class="ri-team-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="fw-semibold text-muted d-block mb-2">Total Clients</span>
                        <h5 class="fw-semibold mb-2">{{ number_format($totalClients) }}</h5>
                        <span class="badge bg-secondary-transparent">All Time</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Packages --}}
    <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-6">
        <div class="card custom-card hrm-main-card warning">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar bg-warning">
                            <i class='bx bx-show-alt fs-18'></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="fw-semibold text-muted d-block mb-2">Active Packages</span>
                        <h5 class="fw-semibold mb-2">{{ number_format($activePackageCount) }}</h5>
                        <span class="badge bg-warning-transparent">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- New Leads --}}
    <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-6">
        <div class="card custom-card hrm-main-card danger">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar bg-danger">
                            <i class="ri-contacts-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="fw-semibold text-muted d-block mb-2">New Leads</span>
                        <h5 class="fw-semibold mb-2">{{ number_format($newLeads) }}</h5>
                        <span class="badge bg-danger-transparent">This Month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif


<!---CHECKER-->
<section class="bg-primary pt-2 mt-3">
    <div class="container main-banner-container">
        <div class="row justify-content-center text-center">
            <div class="col-xxl-7 col-xl-7 col-lg-8">
                <div class="">
                    <h1 class="logo_brand">Frodly</h1>
                    <h5 class="landing-banner-heading text-white mb-3"><span class="text-secondary fw-bold">600+ </span> Users in Bangladesh Rely on Frodly for Easy Parcel Return Management.</h5>
                    <!-- Search Form -->
                    <form class="mb-3 custom-form-group" id="searchForm">
                        <input type="text" id="phoneInput" class="form-control form-control-lg shadow-sm" placeholder="আপনার অর্ডার ডেলিভারি ডেটা দেখতে ফোন নাম্বার লিখুন এবং সার্চ করুন।" aria-label="Phone number" value="{{ optional(auth()->user())->phone }}">
                        <div class="custom-form-btn">
                            <button class="btn btn-primary border-0" type="submit" id="submit-btn"><i class="bi bi-search me-2"></i> Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="row">
    <div class="col-xl-12">
        <!-- Loading / Placeholder -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-3 text-center">
                <div id="loading-screen" style="display:none">
                    <img src="{{ asset('images/loading-200x200x.gif') }}" alt="Loading" class="img-fluid">
                </div>
                <div id="serch-image">
                    <img src="{{ asset('images/find-fraud.png') }}" alt="Find Fraud" class="img-fluid" />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Stats Cards -->
                <div class="row" id="stats-cards" style="display:none">
                    @php
                        $cards = [
                            ['title'=>'সফলতার হার','id'=>'successRate','icon'=>'ri-line-chart-line','bg'=>'info'],
                            ['title'=>'মোট অর্ডার','id'=>'totalOrders','icon'=>'ri-briefcase-2-line','bg'=>'primary'],
                            ['title'=>'মোট ডেলিভারি','id'=>'totalSuccess','icon'=>'ri-wallet-2-line','bg'=>'success'],
                            ['title'=>'মোট বাতিল','id'=>'totalCancel','icon'=>'ri-profile-line','bg'=>'danger'],
                            // ['title'=>'বাতিল হার','id'=>'cancelRate','icon'=>'ri-money-dollar-box-line','bg'=>'warning'],
                            // ['title'=>'রিস্ক লেভেল','id'=>'riskLabel','icon'=>'ri-bill-line','bg'=>'secondary'],
                        ];
                    @endphp
                    @foreach($cards as $card)
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card custom-card border-top-card border-top-{{ $card['bg'] }}">
                            <div class="card-body text-center">
                                <span class="avatar avatar-md bg-{{ $card['bg'] }} shadow-sm avatar-rounded mb-2">
                                    <i class="{{ $card['icon'] }} fs-16"></i>
                                </span>
                                <p class="fs-14 fw-semibold mb-2">{{ $card['title'] }}</p>
                                <h5 class="mb-0 fw-semibold" id="{{ $card['id'] }}">0</h5>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
                <!-- End Cards -->
            </div>
            <div class="col-12">
                <!-- Chart & Table -->
                <div class="row" id="content-data" style="display:none">

                    <div class="col-lg-9 col-md-8 col-sm-12">
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title text-uppercase">Frodly Details</div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover text-center middle-align-table">
                                        <thead class="table-primary text-uppercase">
                                            <tr>
                                                <th class="text-start">কুরিয়ার</th>
                                                <th>অর্ডার</th>
                                                <th>ডেলিভারি</th>
                                                {{-- <th>বাতিল</th> --}}
                                                <th>সফলতার হার</th>
                                            </tr>
                                        </thead>
                                        <tbody id="courierTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <div class="card custom-card text-center">
                            {{-- <div class="card-header">
                                <div class="card-title">Delivery Success Ratio</div>
                            </div> --}}
                            <div class="card-body">
                                <canvas id="successChart" width="150" height="150"></canvas>
                                <span class="badge bg-success fw-bold" id="riskLabel">Great Customer</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Chart & Table -->
            </div>
        </div>


    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
$(function() {
    let chartInstance = null;
    const key = 'savedPhone';
    $('#phoneInput').val(localStorage.getItem(key) || '');
    $('#phoneInput').on('input', e => localStorage.setItem(key, e.target.value));

    $('#searchForm').submit(function(e) {
        e.preventDefault();
        let phone = $('#phoneInput').val().trim();
        if (!phone.match(/^\d{11}$/)) return alert('সঠিক ফোন নাম্বার লিখুন (১১ ডিজিট)');

        $('#loading-screen').show();
        $('#serch-image').hide();
        $('#content-data, #stats-cards').hide();

        $.get("{{ route('get.frodly') }}", { phone: phone }, function(res) {
            if (!res.status) return alert('ডেটা পাওয়া যায়নি');

            $('#loading-screen').hide();
            $('#stats-cards, #content-data').show();

            let { totalSummary, Summaries } = res.data;

            // Update stats cards
            $('#totalOrders').text(totalSummary.total);
            $('#totalSuccess').text(totalSummary.success);
            $('#totalCancel').text(totalSummary.cancel);
            $('#successRate').text(totalSummary.successRate + '%');
            $('#cancelRate').text(totalSummary.cancelRate + '%');

            let riskText = totalSummary.successRate < 50 ? 'High Risk' :
                           totalSummary.successRate < 75 ? 'Moderate Risk' : 'Great Customer';
            $('#riskLabel').text(riskText);

            // Table
            $('#courierTableBody').html(Object.entries(Summaries).map(([courier, data]) => {
                let cancelRate = data.total > 0 ? ((data.success / data.total) * 100).toFixed(1) : '0.0';
                const logo = data.logo || '{{ asset('images/logo-light.svg') }}';
                return `<tr>
                    <td class="text-start d-flex align-items-center gap-2">
                        <img src="${logo}" alt="${courier}" style="height:32px" onerror="this.onerror=null;this.src='{{ asset('images/logo-light.svg') }}';">
                        <span class="fw-semibold">${courier}</span>
                    </td>
                    <td>${data.total}</td>
                    <td>${data.success}</td>
                    <!-- <td>${data.cancel}</td>-->
                    <td><span class="${cancelRate < 30 ? 'badge bg-danger fw-bold' : 'badge bg-success fw-bold'}">${cancelRate}%</span></td>
                </tr>`;
            }).join(''));

            // Animate chart with courier breakdown
            animateChart(totalSummary, Summaries);
        }).fail(() => {
            alert('API কল ব্যর্থ হয়েছে');
            $('#loading-screen').hide();
            $('#serch-image').show();
        });
    });

    // Multi-courier chart with center text
    function animateChart(totalSummary, summaries) {
        const ctx = document.getElementById('successChart').getContext('2d');
        if (chartInstance) chartInstance.destroy();

        const courierNames = Object.keys(summaries);
        const courierSuccess = courierNames.map(courier => summaries[courier].success);
        const colors = [
            '#e83e8c', '#6f42c1', '#20c997', '#dc3545', '#ffc107',
            '#007bff', '#fd7e14', '#6610f2', '#28a745', '#17a2b8'
        ];

        chartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: courierNames,
                datasets: [{
                    label: 'Success Orders',
                    data: courierSuccess,
                    backgroundColor: colors.slice(0, courierNames.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 15,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (tooltipItem) => {
                                let courier = tooltipItem.label;
                                let val = tooltipItem.formattedValue;
                                return `${courier}: ${val} সফল ডেলিভারি`;
                            }
                        }
                    }
                }
            },
            plugins: [{
                id: 'centerText',
                afterDraw(chart) {
                    const { ctx, chartArea: { width, height } } = chart;
                    ctx.save();
                    ctx.font = 'bold 22px Segoe UI';
                    ctx.fillStyle = '#333';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(totalSummary.successRate + '%', width / 2, height / 2);
                    ctx.restore();
                }
            }]
        });
    }
});
</script>
@endpush
