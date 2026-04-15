@extends('layouts.app')

@section('title', 'Danh sách bài tập - ' . $class->name)

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-journal-text me-2 text-primary"></i>Bài tập hàng ngày
            </h1>
            <p class="text-muted mb-0 mt-2">Lớp: <strong>{{ $class->name }}</strong> - Năm học: {{ $class->school_year }}</p>
        </div>
        <div id="header-actions">
            @if(Auth::user()->isAdmin())
                <a href="{{ route('teacher.daily-homework.index') }}" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left me-2"></i>Chọn lớp khác
                </a>
            @endif
            <a href="{{ route('teacher.daily-homework.create') }}?date={{ $selectedDate }}&class_id={{ $class->id }}" 
               class="btn btn-primary" 
               id="create-homework-btn"
               {!! $homework ? 'style="display: none;"' : '' !!}>
                <i class="bi bi-plus-circle me-2"></i>Tạo bài tập mới
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sidebar: Lịch tuần (Dọc) -->
    <div class="col-lg-4 col-xl-3 mb-4">
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-calendar-week me-2"></i>Lịch tuần</h5>
                <p class="text-muted small mb-0 mt-1" id="current-month-display">
                    {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('F Y') }}
                </p>
            </div>
            <div class="card-body p-0">
                <div class="vertical-calendar">
                    @foreach($weekDays as $day)
                        <div class="calendar-day-item {{ $day['isToday'] ? 'today' : '' }} {{ $day['isSelected'] ? 'selected' : '' }} {{ in_array($day['date'], $weekHomework) ? 'has-homework' : '' }}"
                             data-date="{{ $day['date'] }}"
                             onclick="loadHomework('{{ $day['date'] }}')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="day-number-circle me-3">
                                        {{ $day['day'] }}
                                    </div>
                                    <div>
                                        <div class="day-name fw-bold">{{ $day['dayNameVi'] }}</div>
                                        @if($day['isToday'])
                                            <span class="badge bg-primary-soft text-primary x-small">Hôm nay</span>
                                        @endif
                                    </div>
                                </div>
                                @if(in_array($day['date'], $weekHomework))
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Main: Danh sách bài tập -->
    <div class="col-lg-8 col-xl-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-4 px-4">
                <div class="d-flex justify-content-between align-items-md-center flex-column flex-md-row gap-3">
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">
                            Bài tập 
                            <span id="selected-day-name" class="text-primary">{{ \Carbon\Carbon::parse($selectedDate)->locale('vi')->dayName }}</span>
                        </h4>
                        <p class="text-muted mb-0 small" id="selected-date-display">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</p>
                    </div>
                    <div class="d-flex gap-2" id="homework-actions-top">
                        <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" onclick="showZaloModal('{{ $selectedDate }}')">
                            <i class="bi bi-clipboard-check me-2"></i>Copy Zalo
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 pt-0" id="homework-content">
                @if($homework)
                    @if($homework->notes)
                        <div class="alert alert-info border-0 shadow-sm bg-info bg-opacity-10 mb-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle-sm bg-info bg-opacity-20 text-info me-3">
                                    <i class="bi bi-info-lg"></i>
                                </div>
                                <div class="small">
                                    <strong class="text-info">Ghi chú chung:</strong> <span class="text-dark">{{ $homework->notes }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($homework->items->count() > 0)
                        <div class="homework-list" id="homework-items-list">
                            @foreach($homework->items->groupBy('subject_id') as $subjectId => $items)
                                @php 
                                    $item = $items->first();
                                    $combinedContent = $items->pluck('content')->unique()->implode("\n");
                                @endphp
                                <div class="homework-card-wrapper mb-4">
                                    <div class="card border-0 shadow-sm premium-homework-card hover-translate transition">
                                        <div class="card-body p-4">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                                                        <i class="bi bi-book"></i>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                                        <h5 class="mb-0 fw-bold text-dark me-3">{{ $item->subject->name }}</h5>
                                                        @if($item->due_date)
                                                            <span class="badge bg-warning-soft text-warning fw-bold px-3 py-2 rounded-pill">
                                                                <i class="bi bi-calendar-event me-2"></i>Hạn nộp: {{ $item->due_date->format('d/m/Y') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="homework-full-content text-muted lh-base">
                                                        {!! nl2br(e($combinedContent)) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 pt-4 border-top d-flex gap-3 justify-content-center" id="homework-actions-bottom">
                            <a href="{{ route('teacher.daily-homework.edit', $homework) }}" class="btn btn-outline-primary px-4 rounded-pill">
                                <i class="bi bi-pencil me-2"></i>Chỉnh sửa bài tập
                            </a>
                            <button type="button" class="btn btn-danger px-4 rounded-pill" onclick="deleteHomework({{ $homework->id }})">
                                <i class="bi bi-trash me-2"></i>Xóa bài tập
                            </button>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-journals display-4 text-muted opacity-25 mb-3"></i>
                            <h6 class="fw-bold text-muted">Chưa có nội dung bài tập</h6>
                            <a href="{{ route('teacher.daily-homework.create') }}?date={{ $selectedDate }}" class="btn btn-primary btn-sm px-4 rounded-pill mt-2">
                                <i class="bi bi-plus-lg me-2"></i>Giao bài
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-calendar2-x display-4 text-muted opacity-25 mb-3"></i>
                        <h6 class="fw-bold text-muted">Chưa có bài tập cho ngày này</h6>
                        <a href="{{ route('teacher.daily-homework.create') }}?date={{ $selectedDate }}&class_id={{ $class->id }}" class="btn btn-primary btn-sm px-4 rounded-pill mt-2">
                            <i class="bi bi-plus-lg me-2"></i>Tạo bài tập mới
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Vertical Calendar Styles */
    .vertical-calendar {
        display: flex;
        flex-direction: column;
    }
    
    .calendar-day-item {
        padding: 1.25rem 1.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
        position: relative;
    }
    
    .calendar-day-item:hover {
        background-color: #f8fafc;
    }
    
    .calendar-day-item.selected {
        background-color: #f1f5f9;
        border-left: 4px solid #0d6efd;
    }
    
    .calendar-day-item.today {
        background-color: #f8faff;
    }
    
    .day-number-circle {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background-color: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #475569;
        font-size: 1.1rem;
        transition: all 0.2s;
    }
    
    .calendar-day-item.selected .day-number-circle {
        background-color: #0d6efd;
        color: white;
    }
    
    .day-name {
        font-size: 0.95rem;
        color: #334155;
    }
    
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
    .x-small { font-size: 0.75rem; }
    
    /* Homework List Styles */
    .premium-homework-card {
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    
    .hover-translate:hover {
        transform: translateX(8px);
        box-shadow: 0 10px 20px -10px rgba(0,0,0,0.1) !important;
    }
    
    .homework-full-content {
        font-size: 1rem;
        color: #475569;
        white-space: pre-wrap;
    }
    /* Animation */
    @keyframes slideInUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    #homework-content.loading {
        opacity: 0.5;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }

    .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .icon-circle-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
</style>
@endpush

@push('scripts')
<div class="modal fade" id="previewHomeworkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-eye me-2"></i>Xem trước bài tập 
                    <span id="preview-modal-date" class="badge bg-white text-primary ms-2"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div id="preview-modal-content" class="row g-3">
                    <!-- Cards will be rendered here -->
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0 py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success rounded-pill px-4" id="preview-copy-zalo-btn">
                    <i class="bi bi-clipboard-check me-2"></i>Copy Zalo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function loadHomework(date) {
        // Thêm trạng thái loading
        const contentDiv = document.getElementById('homework-content');
        contentDiv.classList.add('loading');

        // Cập nhật sidebar selection
        document.querySelectorAll('.calendar-day-item').forEach(day => {
            day.classList.remove('selected');
            if (day.dataset.date === date) {
                day.classList.add('selected');
            }
        });

        // Cập nhật hiển thị ngày và tháng
        const dateObj = new Date(date);
        const dayNames = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
        const dayNameVi = dayNames[dateObj.getDay()];
        const formattedDate = dateObj.toLocaleDateString('vi-VN');
        
        // Định dạng tháng: "Tháng 2 2026"
        const monthYear = dateObj.toLocaleDateString('vi-VN', { month: 'long', year: 'numeric' });
        const monthDisplay = monthYear.charAt(0).toUpperCase() + monthYear.slice(1);

        document.getElementById('selected-date-display').textContent = formattedDate;
        document.getElementById('selected-day-name').textContent = dayNameVi;
        document.getElementById('current-month-display').textContent = monthDisplay;
        
        // Cập nhật nút "Tạo bài tập mới"
        const createBtn = document.getElementById('create-homework-btn');
        if (createBtn) {
            createBtn.href = `{{ route('teacher.daily-homework.create') }}?date=${date}&class_id={{ $class->id }}`;
        }

        const today = new Date().toISOString().split('T')[0];
        const isFuture = date > today;

        // Load homework via AJAX
        const fetchUrl = `{{ route('teacher.daily-homework.get') }}?class_id={{ $class->id }}&date=${date}`;

        fetch(fetchUrl)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Có lỗi xảy ra');
                    });
                }
                return response.json();
            })
            .then(data => {
                contentDiv.classList.remove('loading');
                
                // Top Actions handler
                const actionTop = document.getElementById('homework-actions-top');
                if (actionTop) {
                    if (isFuture) {
                        actionTop.innerHTML = `
                            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="showPreviewModal('${date}')">
                                <i class="bi bi-eye me-2"></i>Xem trước bài tập
                            </button>
                        `;
                    } else {
                        actionTop.innerHTML = `
                            <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" onclick="showZaloModal('${date}')">
                                <i class="bi bi-clipboard-check me-2"></i>Copy Zalo
                            </button>
                        `;
                    }
                }

                if (data.success && (data.homework || (data.items && data.items.length > 0))) {
                    // Chỉ ẩn nút tạo mới nếu đã có bản ghi bài tập được giao cho ngày này
                    if (createBtn) createBtn.style.display = data.homework ? 'none' : 'inline-block';

                    let html = '';
                    
                    if (data.homework && data.homework.notes) {
                        html += `
                            <div class="alert alert-info border-0 shadow-sm bg-info bg-opacity-10 mb-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle-sm bg-info bg-opacity-20 text-info me-3">
                                        <i class="bi bi-info-lg"></i>
                                    </div>
                                    <div class="small">
                                        <strong class="text-info">Ghi chú chung:</strong> <span class="text-dark">${data.homework.notes}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    if (data.items && data.items.length > 0) {
                        html += '<div class="homework-list" id="homework-items-list">';
                        data.items.forEach((item, index) => {
                            html += `
                                <div class="homework-card-wrapper mb-4" style="animation: slideInUp 0.4s ease forwards; animation-delay: ${index * 0.05}s; opacity: 0;">
                                    <div class="card border-0 shadow-sm premium-homework-card hover-translate transition">
                                        <div class="card-body p-4">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                                                        <i class="bi bi-book"></i>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                                        <h5 class="mb-0 fw-bold text-dark me-3">${item.subject_name}</h5>
                                                        <div>
                                                            ${item.is_due_today ? `<span class="badge bg-danger small rounded-pill px-2 me-1">Đến hạn nộp</span>` : ''}
                                                            ${item.due_date && !item.is_due_today ? `
                                                                <span class="badge bg-warning-soft text-warning fw-bold px-3 py-2 rounded-pill">
                                                                    <i class="bi bi-calendar-event me-2"></i>Hạn nộp: ${item.due_date}
                                                                </span>
                                                            ` : ''}
                                                        </div>
                                                    </div>
                                                    <div class="homework-full-content text-muted lh-base">
                                                        ${item.content.replace(/\n/g, '<br>')}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';

                        // Bottom Actions
                        const isPast = date < today;
                        
                        if (data.homework) {
                            html += `
                                <div class="mt-5 pt-4 border-top d-flex gap-3 justify-content-center">
                                    <a href="/teacher/daily-homework/${data.homework.id}/edit" class="btn btn-outline-primary px-4 rounded-pill">
                                        <i class="bi bi-pencil me-2"></i>Chỉnh sửa bài tập
                                    </a>
                                    <button type="button" class="btn btn-danger px-4 rounded-pill" onclick="deleteHomework(${data.homework.id})">
                                        <i class="bi bi-trash me-2"></i>Xóa bài tập
                                    </button>
                                </div>
                            `;
                        } else {
                            html += `
                                <div class="mt-5 pt-4 border-top text-center">
                                    <a href="/teacher/daily-homework/create?date=${date}&class_id={{ $class->id }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                        <i class="bi bi-plus-lg me-2"></i>Giao thêm bài tập mới
                                    </a>
                                </div>
                            `;
                        }
                    } else {
                        html = `
                            <div class="text-center py-5">
                                <i class="bi bi-journals display-4 text-muted opacity-25 mb-3"></i>
                                <h6 class="fw-bold text-muted">Chưa có nội dung bài tập</h6>
                                <a href="/teacher/daily-homework/create?date=${date}&class_id={{ $class->id }}" class="btn btn-primary btn-sm px-4 rounded-pill mt-2">
                                    <i class="bi bi-plus-lg me-2"></i>Giao bài
                                </a>
                            </div>
                        `;
                    }
                    
                    contentDiv.innerHTML = html;
                } else {
                    // Hiện nút tạo mới ở Header nếu chưa có bài tập
                    if (createBtn) createBtn.style.display = 'inline-block';

                    contentDiv.innerHTML = `
                         <div class="text-center py-5">
                            <i class="bi bi-calendar2-x display-4 text-muted opacity-25 mb-3"></i>
                            <h6 class="fw-bold text-muted">Chưa có bài tập cho ngày này</h6>
                            <a href="/teacher/daily-homework/create?date=${date}&class_id={{ $class->id }}" class="btn btn-primary btn-sm px-4 rounded-pill mt-2">
                                <i class="bi bi-plus-lg me-2"></i>Tạo bài tập mới
                            </a>
                        </div>
                    `;
                }
            })
            .catch(error => {
                contentDiv.classList.remove('loading');
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Không thể tải bài tập. Vui lòng thử lại.',
                });
            });
    }

    function showPreviewModal(date) {
        const modal = new bootstrap.Modal(document.getElementById('previewHomeworkModal'));
        const contentDiv = document.getElementById('preview-modal-content');
        const dateSpan = document.getElementById('preview-modal-date');
        const copyBtn = document.getElementById('preview-copy-zalo-btn');

        // Loading state
        dateSpan.textContent = date.split('-').reverse().join('/');
        contentDiv.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Đang tải dữ liệu...</p></div>';
        copyBtn.onclick = () => showZaloModal(date);

        modal.show();

        // Fetch current day's homework items for preview
        const fetchUrl = `{{ route('teacher.daily-homework.get') }}?class_id={{ $class->id }}&date=${date}`;
        
        fetch(fetchUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.preview_items && data.preview_items.length > 0) {
                    let html = '';
                    data.preview_items.forEach(item => {
                        html += `
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100 rounded-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="fw-bold text-primary mb-0">${item.subject_name}</h6>
                                            <div>
                                                ${item.is_due_today ? `<span class="badge bg-danger small rounded-pill px-2 me-1">Đến hạn nộp</span>` : ''}
                                                ${item.due_date && !item.is_due_today ? `<span class="badge bg-warning-soft text-warning small rounded-pill px-2">Hạn: ${item.due_date}</span>` : ''}
                                            </div>
                                        </div>
                                        <div class="text-muted small lh-base">
                                            ${item.content.replace(/\n/g, '<br>')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    contentDiv.innerHTML = html;
                } else {
                    contentDiv.innerHTML = '<div class="col-12 text-center py-5"><i class="bi bi-folder2-open display-4 text-muted opacity-25"></i><p class="mt-3 text-muted">Ngày này chưa có bài tập nào được giao.</p></div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                contentDiv.innerHTML = '<div class="col-12 text-center py-5 text-danger"><i class="bi bi-exclamation-triangle display-4 opacity-25"></i><p class="mt-3">Có lỗi xảy ra khi tải dữ liệu.</p></div>';
            });
    }

    function showZaloModal(date) {
        // Hiển thị modal với checkbox để chọn các tùy chọn
        Swal.fire({
            title: 'Copy tin nhắn Zalo',
            html: `
                <div class="text-start">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="includeDayAfterNext" checked>
                        <label class="form-check-label" for="includeDayAfterNext">
                            Lấy thêm bài tập ngày hôm sau nữa
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="withAiPlan">
                        <label class="form-check-label" for="withAiPlan">
                            <span class="badge bg-info-soft text-info me-1"><i class="bi bi-robot"></i> AI</span> 
                            Lấy thêm kế hoạch học tập từ AI
                        </label>
                    </div>
                    <p class="text-muted small">Tin nhắn sẽ bao gồm bài tập ngày hôm sau và (nếu chọn) các tùy chọn bổ sung phía trên.</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-clipboard-check me-2"></i>Lấy tin nhắn',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#198754',
            didOpen: () => {
                // Không cần làm gì
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const includeDayAfterNext = document.getElementById('includeDayAfterNext').checked;
                const withAiPlan = document.getElementById('withAiPlan').checked;
                loadZaloMessage(date, includeDayAfterNext, withAiPlan);
            }
        });
    }

    function loadZaloMessage(date, includeDayAfterNext, withAiPlan) {
        // Hiển thị loading
        Swal.fire({
            title: 'Đang chuẩn bị tin nhắn...',
            html: withAiPlan ? 'AI đang phân tích và lập kế hoạch (có thể mất 5-10s)...' : 'Vui lòng đợi trong giây lát',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        const url = `{{ route('teacher.daily-homework.zalo-message') }}?class_id={{ $class->id }}&date=${date}&include_day_after_next=${includeDayAfterNext ? 1 : 0}&with_ai_plan=${withAiPlan ? 1 : 0}`;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Có lỗi xảy ra');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showZaloMessageModal(data.message);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: data.message || 'Không thể tải tin nhắn.',
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Không thể tải tin nhắn. Vui lòng thử lại.',
                });
            });
    }

    function showZaloMessageModal(message) {
        Swal.fire({
            title: 'Tin nhắn Zalo',
            html: `
                <div class="text-start">
                    <textarea id="zalo-message-text" class="form-control" rows="15" readonly style="font-family: monospace; white-space: pre-wrap; resize: vertical;">${escapeHtml(message)}</textarea>
                    <small class="text-muted mt-2 d-block">Click vào ô text để chọn toàn bộ, sau đó copy (Ctrl+C)</small>
                </div>
            `,
            width: '700px',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-clipboard-check me-2"></i>Copy',
            cancelButtonText: 'Đóng',
            confirmButtonColor: '#198754',
            didOpen: () => {
                const textarea = document.getElementById('zalo-message-text');
                textarea.focus();
                textarea.select();
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const textarea = document.getElementById('zalo-message-text');
                textarea.select();
                
                // Sử dụng Clipboard API nếu có, nếu không thì dùng execCommand
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(message).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Đã copy!',
                            text: 'Tin nhắn đã được copy vào clipboard',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }).catch(() => {
                        // Fallback nếu clipboard API không hoạt động
                        document.execCommand('copy');
                        Swal.fire({
                            icon: 'success',
                            title: 'Đã copy!',
                            text: 'Tin nhắn đã được copy vào clipboard',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    });
                } else {
                    // Fallback cho trình duyệt cũ
                    document.execCommand('copy');
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã copy!',
                        text: 'Tin nhắn đã được copy vào clipboard',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function deleteHomework(homeworkId) {
        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa bài tập này? Hành động này không thể hoàn tác.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash me-2"></i>Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tạo form để submit DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/teacher/daily-homework/${homeworkId}`;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken.getAttribute('content');
                    form.appendChild(csrfInput);
                }
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush
@endsection

