@extends('layouts.app')

@section('title', 'Bảng điều khiển')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 12px;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">
            <i class="bi bi-grid-1x2-fill me-2 text-primary"></i>Phân tích Tổng quan
        </h1>
        <div class="text-muted small">
            <i class="bi bi-calendar3 me-1"></i> Hôm nay: {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row">
        <!-- Total Classes Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100 py-2 stat-card border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2 text-start">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng số lớp</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalClasses }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coverage Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow-sm h-100 py-2 stat-card border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2 text-start">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1" title="Số môn đã giao bài / Số môn theo thời khóa biểu hôm nay">
                                Tỷ lệ bao phủ <i class="bi bi-info-circle ms-1 small"></i>
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $coverageRate }}%</div>
                                    <div class="text-muted small mt-1">{{ $totalSubjectsWithHomework }}/{{ $totalExpectedSubjects }} môn</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: {{ $coverageRate }}%" aria-valuenow="{{ $coverageRate }}" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-bar-chart-fill fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Homework Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow-sm h-100 py-2 stat-card border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2 text-start">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Số đầu bài tập
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalHomeworkItemsToday }}</div>
                            <div class="text-muted small mt-1">Giao trong ngày</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-journal-check fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Classes Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow-sm h-100 py-2 stat-card border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2 text-start">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Lớp chưa có bài</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $classesWithoutHomeworkCount }}</div>
                            <div class="text-muted small mt-1">Của hôm nay</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Weekly Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm mb-4 border-0" style="border-radius: 15px;">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold">Dữ liệu bao phủ bài tập (7 ngày)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="weeklyCoverageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject Distribution Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm mb-4 border-0" style="border-radius: 15px;">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold">Phân bổ theo môn học</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="subjectDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Status Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm mb-4 border-0" style="border-radius: 15px;">
                <div class="card-header py-3 bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold">Chi tiết tình trạng theo lớp (Hôm nay)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Tên lớp</th>
                                    <th>Năm học</th>
                                    <th class="text-center">Môn có bài / Tổng</th>
                                    <th class="text-center">Tiến độ bao phủ</th>
                                    <th class="text-end pe-4">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classesStatus as $class)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle-sm bg-primary bg-opacity-10 me-2">
                                                <span class="text-primary fw-bold small">{{ substr($class['name'], 0, 2) }}</span>
                                            </div>
                                            <span class="fw-bold">{{ $class['name'] }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $class['school_year'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark fw-normal border">
                                            {{ $class['subject_count'] }}/{{ $class['expected_count'] }} môn
                                        </span>
                                    </td>
                                    <td class="text-center" style="width: 250px;">
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar bg-primary shadow-sm" role="progressbar" 
                                                    style="width: {{ $class['coverage_rate'] }}%" 
                                                    aria-valuenow="{{ $class['coverage_rate'] }}" 
                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="ms-2 small fw-bold text-muted" style="width: 40px;">{{ $class['coverage_rate'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($class['coverage_rate'] == 100)
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                                                <i class="bi bi-check-all me-1"></i>Hoàn thành
                                            </span>
                                        @elseif($class['has_homework'])
                                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3">
                                                <i class="bi bi-check me-1"></i>Đã giao
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3">
                                                <i class="bi bi-clock me-1"></i>Đang chờ
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Weekly Coverage Chart (Bar)
    const ctxWeekly = document.getElementById('weeklyCoverageChart').getContext('2d');
    const weeklyData = @json($last7Days);
    
    new Chart(ctxWeekly, {
        type: 'bar',
        data: {
            labels: weeklyData.map(d => d.label),
            datasets: [{
                label: 'Tỷ lệ bao phủ (%)',
                data: weeklyData.map(d => d.rate),
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                hoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                borderColor: '#4e73df',
                borderWidth: 1,
                borderRadius: 5,
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Subject Distribution Chart (Doughnut)
    const ctxSubject = document.getElementById('subjectDistributionChart').getContext('2d');
    const subjectData = @json($subjectDistribution);
    
    new Chart(ctxSubject, {
        type: 'doughnut',
        data: {
            labels: subjectData.map(d => d.name),
            datasets: [{
                data: subjectData.map(d => d.total),
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
                    '#858796', '#5a5c69', '#6610f2', '#fd7e14', '#20c997'
                ],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
                cutout: '70%'
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
                }
            }
        }
    });
</script>
@endpush


