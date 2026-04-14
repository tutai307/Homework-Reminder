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
<div class="container-fluid py-4 position-relative">
    <!-- Loading Overlay -->
    <div id="dashboard-loading" class="position-fixed top-0 start-0 w-100 vh-100 flex-column align-items-center justify-content-center bg-white bg-opacity-75" style="z-index: 1050; display: none !important;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <span class="mt-3 fw-medium text-muted">Đang tải dữ liệu...</span>
    </div>
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
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="stat-coverage-rate">{{ $coverageRate }}%</div>
                                    <div class="text-muted small mt-1" id="stat-coverage-ratio">{{ $totalSubjectsWithHomework }}/{{ $totalExpectedSubjects }} môn</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2" style="height: 6px;">
                                        <div id="stat-coverage-bar" class="progress-bar bg-success" role="progressbar"
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-homework-items">{{ $totalHomeworkItemsToday }}</div>
                            <div class="text-muted small mt-1" id="stat-hw-label">Ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m') }}</div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="stat-pending-classes">{{ $classesWithoutHomeworkCount }}</div>
                            <div class="text-muted small mt-1" id="stat-pending-label">Ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m') }}</div>
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
                    <h6 id="subject-chart-title" class="m-0 font-weight-bold text-primary fw-bold">Phân bổ theo môn học (Ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m') }})</h6>
                </div>
                <div class="card-body">
                    <div id="subject-chart-wrapper" class="chart-container {{ count($subjectDistribution) > 0 ? '' : 'd-none' }}">
                        <canvas id="subjectDistributionChart"></canvas>
                    </div>
                    
                    <div id="subject-chart-empty" class="flex-column align-items-center justify-content-center text-muted {{ count($subjectDistribution) > 0 ? 'd-none' : 'd-flex' }}" style="height: 300px;">
                        <i class="bi bi-pie-chart text-gray-300 mb-3" style="font-size: 3.5rem;"></i>
                        <p class="mb-0">Chưa có dữ liệu bài tập trong ngày</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Status Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm mb-4 border-0" style="border-radius: 15px;">
                <div class="card-header py-3 bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 id="class-status-title" class="m-0 font-weight-bold text-primary fw-bold">Chi tiết tình trạng theo lớp (Ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m') }})</h6>
                    <button id="btn-back-today" onclick="fetchDashboardData('{{ $today }}')" class="btn btn-sm btn-light border text-muted" style="display: {{ $selectedDate !== $today ? 'inline-block' : 'none' }}">
                        <i class="bi bi-arrow-counterclockwise"></i> Về hôm nay
                    </button>
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
                            <tbody id="class-status-tbody">
                                @include('admin.dashboard.partials.class_status_table', ['classesStatus' => $classesStatus])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Logs Snapshot -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm mb-4 border-0" style="border-radius: 15px;">
                <div class="card-header py-3 bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold">Nhật ký hoạt động</h6>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Thời gian</th>
                                    <th>Mô tả hành động</th>
                                    <th>Tài khoản</th>
                                    <th>Nhóm Quyền</th>
                                    <th>Lớp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                <tr>
                                    <td class="ps-4 text-nowrap">
                                        <span class="fw-medium text-dark">{{ $log->created_at->format('H:i') }}</span>
                                        <span class="text-muted small ms-1">{{ $log->created_at->format('d/m/y') }}</span>
                                    </td>
                                    <td>
                                        @if($log->action === 'create_homework')
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 me-2"><i class="bi bi-journal-plus me-1"></i>Giao bài</span>
                                        @elseif($log->action === 'view_homework')
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 me-2"><i class="bi bi-eye me-1"></i>Xem bài</span>
                                        @elseif($log->action === 'update_homework')
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 me-2"><i class="bi bi-pencil-square me-1"></i>Sửa bài</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 me-2"><i class="bi bi-activity me-1"></i>Khác</span>
                                        @endif
                                        <span class="text-muted">{{ $log->description }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle-sm bg-primary bg-opacity-10 me-2 text-primary fw-bold small">
                                                {{ substr($log->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $log->user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($log->user->isTeacher())
                                            <span class="badge bg-success">Giáo viên</span>
                                        @elseif($log->user->isClassMonitor())
                                            <span class="badge bg-info text-dark">Lớp trưởng</span>
                                        @elseif($log->user->isAcademicSubMonitor())
                                            <span class="badge bg-info text-dark">Lớp phó</span>
                                        @elseif($log->user->isAdmin())
                                            <span class="badge bg-danger">Admin</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $log->user->role }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->class)
                                            <span class="fw-medium text-primary">{{ $log->class->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <span class="text-muted">Chưa có dữ liệu nhật ký nào.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chi tiết bài tập -->
<div class="modal fade" id="classHomeworkModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold text-primary" id="classHomeworkModalTitle">Chi tiết bài tập</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body position-relative p-0" id="classHomeworkModalBody">
         <!-- Content appended via JS -->
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Weekly Coverage Chart (Bar + Multi-axis Line)
    const ctxWeekly = document.getElementById('weeklyCoverageChart').getContext('2d');
    const weeklyData = @json($last7Days);
    
    new Chart(ctxWeekly, {
        data: {
            labels: weeklyData.map(d => d.label),
            datasets: [
                {
                    type: 'bar',
                    label: 'Tỷ lệ (%)',
                    data: weeklyData.map(d => d.rate),
                    backgroundColor: 'rgba(78, 115, 223, 0.2)',
                    hoverBackgroundColor: 'rgba(78, 115, 223, 0.4)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1,
                    borderRadius: 5,
                    yAxisID: 'y',
                    order: 3
                },
                {
                    type: 'line',
                    label: 'Thực tế (môn)',
                    data: weeklyData.map(d => d.actual),
                    borderColor: '#1cc88a',
                    backgroundColor: '#1cc88a',
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#1cc88a',
                    yAxisID: 'y1',
                    order: 1
                },
                {
                    type: 'line',
                    label: 'Dự kiến (môn)',
                    data: weeklyData.map(d => d.expected),
                    borderColor: '#f6c23e',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#f6c23e',
                    yAxisID: 'y1',
                    order: 2
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            onClick: function(e, activeElements) {
                if (activeElements.length > 0) {
                    const dataIndex = activeElements[0].index;
                    const clickedDate = weeklyData[dataIndex].date;
                    if (clickedDate) {
                        fetchDashboardData(clickedDate);
                    }
                }
            },
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { 
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        font: { size: 11 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#5a5c69',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyColor: '#858796',
                    borderColor: '#e3e6f0',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 4,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y;
                                if (context.datasetIndex === 0) {
                                    label += '%';
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Tỷ lệ (%)',
                        color: '#4e73df',
                        font: { size: 11 }
                    },
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Số môn học',
                        color: '#858796',
                        font: { size: 11 }
                    },
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Subject Distribution Chart (Doughnut)
    let subjectChartInstance = null;
    const ctxSubject = document.getElementById('subjectDistributionChart').getContext('2d');
    
    @if(count($subjectDistribution) > 0)
    const subjectData = @json($subjectDistribution);
    
    subjectChartInstance = new Chart(ctxSubject, {
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
                        padding: 15,
                        font: { size: 11 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#5a5c69',
                    bodyColor: '#858796',
                    borderColor: '#e3e6f0',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return ` ${label}: ${value} bài (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    @endif

    window.fetchDashboardData = function(date) {
        document.getElementById('dashboard-loading').style.setProperty('display', 'flex', 'important');
        
        fetch(`?date=${date}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            setTimeout(() => {
                document.getElementById('subject-chart-title').innerText = `Phân bổ theo môn học (Ngày ${data.selectedDateTitle})`;
                document.getElementById('class-status-title').innerText = `Chi tiết tình trạng theo lớp (Ngày ${data.selectedDateTitle})`;
                document.getElementById('btn-back-today').style.display = data.isToday ? 'none' : 'inline-block';
                document.getElementById('class-status-tbody').innerHTML = data.tableHtml;
                
                // Cập nhật các The thống kê
                document.getElementById('stat-coverage-rate').innerText = `${data.coverageRate}%`;
                document.getElementById('stat-coverage-ratio').innerText = `${data.totalSubjectsWithHomework}/${data.totalExpectedSubjects} môn`;
                document.getElementById('stat-coverage-bar').style.width = `${data.coverageRate}%`;
                
                document.getElementById('stat-homework-items').innerText = data.totalHomeworkItemsToday;
                document.getElementById('stat-hw-label').innerText = `Ngày ${data.selectedDateTitle}`;
                
                document.getElementById('stat-pending-classes').innerText = data.classesWithoutHomeworkCount;
                document.getElementById('stat-pending-label').innerText = `Ngày ${data.selectedDateTitle}`;
                
                if (data.subjectDistribution.length > 0) {
                    document.getElementById('subject-chart-empty').classList.remove('d-flex');
                    document.getElementById('subject-chart-empty').classList.add('d-none');
                    document.getElementById('subject-chart-wrapper').classList.remove('d-none');
                    
                    const subjectData = data.subjectDistribution;
                    
                    if (subjectChartInstance) {
                        subjectChartInstance.data.labels = subjectData.map(d => d.name);
                        subjectChartInstance.data.datasets[0].data = subjectData.map(d => d.total);
                        subjectChartInstance.update();
                    } else {
                        subjectChartInstance = new Chart(ctxSubject, {
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
                                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, font: { size: 11 } } },
                                    tooltip: {
                                        backgroundColor: 'rgba(255,255,255,0.95)', titleColor: '#5a5c69',
                                        bodyColor: '#858796', borderColor: '#e3e6f0', borderWidth: 1, padding: 10,
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.parsed;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = Math.round((value / total) * 100);
                                                return ` ${label}: ${value} bài (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                } else {
                    document.getElementById('subject-chart-empty').classList.remove('d-none');
                    document.getElementById('subject-chart-empty').classList.add('d-flex');
                    document.getElementById('subject-chart-wrapper').classList.add('d-none');
                }
                
                // Xóa loading layer khi hoàn tất
                document.getElementById('dashboard-loading').style.setProperty('display', 'none', 'important');
            }, 500); // Thêm delay 0.5s cho đẹp
        })
        .catch(err => {
            console.error(err);
            document.getElementById('dashboard-loading').style.setProperty('display', 'none', 'important');
            alert('Quá trình kết nối tới hệ thống có lỗi xảy ra. Hãy thử lại!');
        });
    };
    window.viewClassHomework = function(classId, date) {
        const modalEl = document.getElementById('classHomeworkModal');
        const modal = new bootstrap.Modal(modalEl);
        
        document.getElementById('classHomeworkModalBody').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted small">Đang tải dữ liệu...</div>
            </div>
        `;
        document.getElementById('classHomeworkModalTitle').innerText = `Chi tiết bài tập`;
        modal.show();

        fetch(`/admin/dashboard/class-homework?class_id=${classId}&date=${date}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('classHomeworkModalTitle').innerText = data.title;
            document.getElementById('classHomeworkModalBody').innerHTML = data.html;
        })
        .catch(err => {
            console.error(err);
            document.getElementById('classHomeworkModalBody').innerHTML = `
                <div class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fs-3"></i>
                    <p class="mt-2 text-muted">Đã có lỗi xảy ra. Hãy thử lại.</p>
                </div>
            `;
        });
    }
</script>
@endpush


