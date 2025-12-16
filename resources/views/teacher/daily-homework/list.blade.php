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
        <div>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('teacher.daily-homework.index') }}" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left me-2"></i>Chọn lớp khác
                </a>
            @endif
            <a href="{{ route('teacher.daily-homework.create') }}?date={{ $selectedDate }}" class="btn btn-primary" id="create-homework-btn">
                <i class="bi bi-plus-circle me-2"></i>Tạo bài tập mới
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Lịch tuần -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Lịch tuần</h5>
            </div>
            <div class="card-body p-0">
                <div class="week-calendar">
                    @foreach($weekDays as $day)
                        <div class="calendar-day {{ $day['isToday'] ? 'today' : '' }} {{ $day['isSelected'] ? 'selected' : '' }} {{ in_array($day['date'], $weekHomework) ? 'has-homework' : '' }}"
                             data-date="{{ $day['date'] }}"
                             onclick="loadHomework('{{ $day['date'] }}')">
                            <div class="day-header">
                                <span class="day-name">{{ $day['dayNameVi'] }}</span>
                                @if($day['isToday'])
                                    <span class="badge bg-primary">Hôm nay</span>
                                @endif
                            </div>
                            <div class="day-number">{{ $day['day'] }}</div>
                            @if(in_array($day['date'], $weekHomework))
                                <div class="homework-indicator">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách bài tập -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>Bài tập ngày 
                    <span id="selected-date-display">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</span>
                    (<span id="selected-day-name">{{ \Carbon\Carbon::parse($selectedDate)->locale('vi')->dayName }}</span>)
                </h5>
            </div>
            <div class="card-body" id="homework-content">
                @if($homework)
                    @if($homework->notes)
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i><strong>Ghi chú:</strong> {{ $homework->notes }}
                        </div>
                    @endif

                    @if($homework->items->count() > 0)
                        <div class="homework-items">
                            @foreach($homework->items as $item)
                                <div class="homework-item mb-3">
                                    <div class="card border-start border-4 border-primary">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-book me-2 text-primary"></i>
                                                    <strong>{{ $item->subject->name }}</strong>
                                                </h6>
                                                @if($item->due_date)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-calendar-event me-1"></i>
                                                        Hạn: {{ $item->due_date->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="mb-0 text-muted">{{ $item->content }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex gap-2">
                            <a href="{{ route('teacher.daily-homework.edit', $homework) }}" class="btn btn-outline-primary">
                                <i class="bi bi-pencil me-2"></i>Chỉnh sửa bài tập
                            </a>
                            <button type="button" class="btn btn-success" onclick="showZaloModal('{{ $selectedDate }}')">
                                <i class="bi bi-clipboard-check me-2"></i>Copy Zalo
                            </button>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <p class="text-muted mt-3">Chưa có bài tập nào cho ngày này</p>
                            <a href="{{ route('teacher.daily-homework.create') }}?date={{ $selectedDate }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Tạo bài tập cho ngày này
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <p class="text-muted mt-3">Chưa có bài tập nào cho ngày này</p>
                        <a href="{{ route('teacher.daily-homework.create') }}?date={{ $selectedDate }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Tạo bài tập cho ngày này
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .week-calendar {
        display: flex;
        flex-direction: column;
    }

    .calendar-day {
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
    }

    .calendar-day:last-child {
        border-bottom: none;
    }

    .calendar-day:hover {
        background-color: #f8f9fa;
    }

    .calendar-day.today {
        background-color: #e7f3ff;
        border-left: 4px solid #0d6efd;
    }

    .calendar-day.selected {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .calendar-day.has-homework::after {
        content: '';
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 8px;
        height: 8px;
        background-color: #198754;
        border-radius: 50%;
    }

    .calendar-day.selected.has-homework::after {
        background-color: white;
    }

    .day-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .day-name {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .day-number {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .homework-indicator {
        position: absolute;
        bottom: 0.5rem;
        right: 0.5rem;
    }

    .homework-item {
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function loadHomework(date) {
        // Cập nhật selected date
        document.querySelectorAll('.calendar-day').forEach(day => {
            day.classList.remove('selected');
            if (day.dataset.date === date) {
                day.classList.add('selected');
            }
        });

        // Cập nhật hiển thị ngày
        const dateObj = new Date(date);
        const dayNames = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
        const dayNameVi = dayNames[dateObj.getDay()];
        const formattedDate = dateObj.toLocaleDateString('vi-VN');

        document.getElementById('selected-date-display').textContent = formattedDate;
        document.getElementById('selected-day-name').textContent = dayNameVi;
        
        // Cập nhật nút "Tạo bài tập mới" với ngày được chọn
        const createBtn = document.getElementById('create-homework-btn');
        if (createBtn) {
            createBtn.href = `{{ route('teacher.daily-homework.create') }}?date=${date}`;
        }

        // Load homework via AJAX
        @if(Auth::user()->isAdmin())
            fetch(`{{ route('teacher.daily-homework.get') }}?class_id={{ $class->id }}&date=${date}`)
        @else
            fetch(`{{ route('teacher.daily-homework.get') }}?date=${date}`)
        @endif
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Có lỗi xảy ra');
                    });
                }
                return response.json();
            })
            .then(data => {
                const contentDiv = document.getElementById('homework-content');
                
                if (data.success && data.homework) {
                    let html = '';
                    
                    if (data.homework.notes) {
                        html += `<div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i><strong>Ghi chú:</strong> ${data.homework.notes}
                        </div>`;
                    }

                    if (data.items && data.items.length > 0) {
                        html += '<div class="homework-items">';
                        data.items.forEach(item => {
                            html += `
                                <div class="homework-item mb-3">
                                    <div class="card border-start border-4 border-primary">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-book me-2 text-primary"></i>
                                                    <strong>${item.subject_name}</strong>
                                                </h6>
                                                ${item.due_date ? `<span class="badge bg-warning text-dark">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    Hạn: ${item.due_date}
                                                </span>` : ''}
                                            </div>
                                            <p class="mb-0 text-muted">${item.content}</p>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        // Kiểm tra xem có phải ngày hôm nay không
                        const today = new Date().toISOString().split('T')[0];
                        const isToday = date === today;
                        
                        let actionButtons = `
                            <div class="mt-4 pt-3 border-top d-flex gap-2 flex-wrap">
                                <a href="/teacher/daily-homework/${data.homework.id}/edit" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil me-2"></i>Chỉnh sửa bài tập
                                </a>
                                <button type="button" class="btn btn-success" onclick="showZaloModal('${date}')">
                                    <i class="bi bi-clipboard-check me-2"></i>Copy Zalo
                                </button>
                        `;
                        
                        if (isToday) {
                            actionButtons += `
                                <button type="button" class="btn btn-danger" onclick="deleteHomework(${data.homework.id})">
                                    <i class="bi bi-trash me-2"></i>Xóa bài tập
                                </button>
                            `;
                        }
                        
                        actionButtons += `</div>`;
                        html += actionButtons;
                    } else {
                        html = `
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <p class="text-muted mt-3">Chưa có bài tập nào cho ngày này</p>
                                <a href="/teacher/daily-homework/create?date=${date}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Tạo bài tập cho ngày này
                                </a>
                            </div>
                        `;
                    }
                    
                    contentDiv.innerHTML = html;
                } else {
                    contentDiv.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <p class="text-muted mt-3">Chưa có bài tập nào cho ngày này</p>
                                <a href="/teacher/daily-homework/create?date=${date}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Tạo bài tập cho ngày này
                                </a>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Không thể tải bài tập. Vui lòng thử lại.',
                });
            });
    }

    function showZaloModal(date) {
        // Hiển thị modal với checkbox để chọn có lấy bài tập hôm sau nữa không
        Swal.fire({
            title: 'Copy tin nhắn Zalo',
            html: `
                <div class="text-start">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="includeDayAfterNext" checked>
                        <label class="form-check-label" for="includeDayAfterNext">
                            Lấy thêm bài tập ngày hôm sau nữa
                        </label>
                    </div>
                    <p class="text-muted small">Tin nhắn sẽ bao gồm bài tập ngày hôm sau và (nếu chọn) ngày hôm sau nữa</p>
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
                loadZaloMessage(date, includeDayAfterNext);
            }
        });
    }

    function loadZaloMessage(date, includeDayAfterNext) {
        // Hiển thị loading
        Swal.fire({
            title: 'Đang tải tin nhắn...',
            html: 'Vui lòng đợi trong giây lát',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        @if(Auth::user()->isAdmin())
            const url = `{{ route('teacher.daily-homework.zalo-message') }}?class_id={{ $class->id }}&date=${date}&include_day_after_next=${includeDayAfterNext ? 1 : 0}`;
        @else
            const url = `{{ route('teacher.daily-homework.zalo-message') }}?date=${date}&include_day_after_next=${includeDayAfterNext ? 1 : 0}`;
        @endif

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

