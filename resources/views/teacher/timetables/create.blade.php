@extends('layouts.app')

@section('title', 'Thời khóa biểu - ' . $class->name)

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-table me-2 text-primary"></i>Thiết lập thời khóa biểu
            </h1>
            <p class="text-muted mb-0 mt-2">Lớp: <strong class="text-dark">{{ $class->name }}</strong> - Năm học: <span class="badge bg-soft-primary text-primary">{{ $class->school_year }}</span></p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-soft-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#aiImportModal">
                <i class="bi bi-robot me-1"></i>Nhập từ hình ảnh (AI)
            </button>
            <input type="file" id="aiImageInput" accept="image/*" class="d-none">
            <a href="{{ route('teacher.timetables.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="bi bi-arrow-left me-1"></i>Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Modal Hướng dẫn & Nhập AI -->
<div class="modal fade" id="aiImportModal" tabindex="-1" aria-labelledby="aiImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aiImportModalLabel"><i class="bi bi-robot me-2 text-primary"></i>Nhập thời khóa biểu bằng AI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-1"></i>Hướng dẫn mẫu ảnh</h6>
                        <p class="small text-muted">Hệ thống AI hoạt động tốt nhất với ảnh có cấu trúc như sau:</p>
                        <ul class="small ps-3">
                            <li>Bảng chia 2 phần: <strong>Buổi sáng</strong> và <strong>Buổi chiều</strong>.</li>
                            <li>Tiêu đề cột là: <strong>THỨ 2, THỨ 3... THỨ 7</strong>.</li>
                            <li>Ảnh rõ nét, không bị nghiêng hoặc mờ.</li>
                        </ul>
                        <div class="bg-light p-2 rounded text-center mb-3">
                            <span class="text-muted small">Mẫu thời khóa biểu lý tưởng</span>
                            <img src="{{ asset('storage/images/tkb_test.png') }}" class="img-fluid rounded border mt-2" alt="Sample Timetable">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3"><i class="bi bi-upload me-1"></i>Tải ảnh lên hoặc Paste</h6>
                        <div id="pasteArea" class="border border-dashed rounded p-4 text-center bg-light" style="cursor: pointer; min-height: 200px;">
                            <i class="bi bi-image text-muted display-4"></i>
                            <p class="mt-2 mb-0">Click để chọn ảnh hoặc <br><strong>Ctrl + V</strong> để dán ảnh</p>
                        </div>
                        <div class="alert alert-warning mt-3 small mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Lưu ý:</strong> AI có thể nhận diện sai một số môn học. Vui lòng <strong>kiểm tra kỹ lại</strong> bảng sau khi AI nhập xong trước khi nhấn Lưu.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Chi tiết lịch học hàng tuần</h5>
    </div>
    <div class="card-body p-0">
        <form action="{{ route('teacher.timetables.store', $class) }}" method="POST" class="needs-validation" novalidate>
            @csrf
            
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 timetable-grid">
                    <thead class="bg-light text-center small text-uppercase fw-bold text-muted">
                        <tr>
                            <th class="py-3" style="width: 100px; background-color: #f8f9fa;">Tiết</th>
                            <th class="py-3">Thứ 2</th>
                            <th class="py-3">Thứ 3</th>
                            <th class="py-3">Thứ 4</th>
                            <th class="py-3">Thứ 5</th>
                            <th class="py-3">Thứ 6</th>
                            <th class="py-3">Thứ 7</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($period = 1; $period <= 10; $period++)
                            <tr>
                                <td class="fw-bold text-center bg-light text-primary small">
                                    Tiết {{ $period }}
                                </td>
                                @for($weekday = 1; $weekday <= 6; $weekday++)
                                    <td class="p-2 timetable-cell" data-weekday="{{ $weekday }}" data-period="{{ $period }}">
                                        @php
                                            $oldValue = old("timetable.$weekday.$period", $timetables[$weekday][$period] ?? '');
                                        @endphp
                                        <div class="draggable-wrapper" draggable="true">
                                            <div class="drag-handle">
                                                <i class="bi bi-grip-vertical"></i>
                                            </div>
                                            <select name="timetable[{{ $weekday }}][{{ $period }}]" 
                                                    class="form-select select2" data-placeholder="">
                                                <option value=""></option>
                                                @foreach($subjects as $subject)
                                                    <option value="{{ $subject->id }}"
                                                        {{ $oldValue == $subject->id ? 'selected' : '' }}>
                                                        {{ $subject->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-top p-4 d-flex justify-content-between">
                <a href="{{ route('teacher.timetables.index') }}" class="btn btn-light px-4 border">
                    <i class="bi bi-x-circle me-1"></i>Hủy bỏ
                </a>
                <button type="submit" class="btn btn-primary px-5 shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i>Lưu thời khóa biểu
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .timetable-grid th { border-top: none; }
    .timetable-grid td { transition: background-color 0.2s; position: relative; }
    .timetable-grid td:hover { background-color: rgba(13, 110, 253, 0.02); }
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    
    /* Drag and Drop Styles */
    .drag-handle { 
        cursor: grab; 
        color: #adb5bd;
        padding-right: 4px;
        transition: color 0.2s;
    }
    .drag-handle:hover { color: #0d6efd; }
    .drag-handle:active { cursor: grabbing; }
    
    .draggable-wrapper {
        display: flex;
        align-items: center;
        width: 100%;
    }
    
    .drop-target-active {
        background-color: rgba(13, 110, 253, 0.1) !important;
        box-shadow: inset 0 0 0 2px #0d6efd;
    }

    .dragging { opacity: 0.5; }

    /* Select2 Skinning */
    .select2-container--default .select2-selection--single {
        border: 1px solid #dee2e6;
        height: 38px;
        border-radius: 6px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        font-size: 0.875rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>
@endpush
@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        width: '100%',
        allowClear: true
    });

    const aiImageInput = $('#aiImageInput');
    const pasteArea = $('#pasteArea');
    const aiImportModal = new bootstrap.Modal(document.getElementById('aiImportModal'));

    // --- Drag and Drop Logic ---
    let draggedSource = null;

    $('.timetable-cell').on('dragstart', function(e) {
        draggedSource = this;
        $(this).addClass('dragging');
        
        const weekday = $(this).data('weekday');
        const period = $(this).data('period');
        const subjectId = $(this).find('select').val();

        e.originalEvent.dataTransfer.effectAllowed = 'move';
        e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify({
            weekday, period, subjectId
        }));
    });

    $('.timetable-cell').on('dragend', function() {
        $(this).removeClass('dragging');
        $('.timetable-cell').removeClass('drop-target-active');
    });

    $('.timetable-cell').on('dragover', function(e) {
        e.preventDefault();
        e.originalEvent.dataTransfer.dropEffect = 'move';
        return false;
    });

    $('.timetable-cell').on('dragenter', function() {
        if (this !== draggedSource) {
            $(this).addClass('drop-target-active');
        }
    });

    $('.timetable-cell').on('dragleave', function() {
        $(this).removeClass('drop-target-active');
    });

    $('.timetable-cell').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drop-target-active');

        if (this === draggedSource) return;

        const sourceData = JSON.parse(e.originalEvent.dataTransfer.getData('text/plain'));
        const targetWeekday = $(this).data('weekday');
        const targetPeriod = $(this).data('period');
        const targetSelect = $(this).find('select');
        const targetSubjectId = targetSelect.val();

        const sourceSelect = $(draggedSource).find('select');

        // Swap values
        sourceSelect.val(targetSubjectId).trigger('change');
        targetSelect.val(sourceData.subjectId).trigger('change');

        return false;
    });

    // --- AI Import Logic ---

    // Click to upload from modal
    pasteArea.on('click', function() {
        aiImageInput.click();
    });

    // Handle File Selection
    aiImageInput.on('change', function(e) {
        if (!e.target.files.length) return;
        processImage(e.target.files[0]);
    });

    // Handle Paste Event
    window.addEventListener('paste', function(e) {
        // Only handle paste if modal is open
        if (!$('#aiImportModal').hasClass('show')) return;

        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let index in items) {
            const item = items[index];
            if (item.kind === 'file' && item.type.indexOf('image/') !== -1) {
                const blob = item.getAsFile();
                processImage(blob);
                break;
            }
        }
    });

    function processImage(file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const base64Image = event.target.result;

            // Close modal and show loading
            aiImportModal.hide();
            
            Swal.fire({
                title: 'Đang phân tích hình ảnh...',
                text: 'Hệ thống AI đang trích xuất dữ liệu, vui lòng đợi.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route('teacher.timetables.import-image', $class) }}',
                method: 'POST',
                global: false, // Disable global preloader for this request
                data: {
                    _token: '{{ csrf_token() }}',
                    image: base64Image
                },
                success: function(response) {
                    if (response.success && response.data) {
                        applyTimetableData(response.data);
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công!',
                            text: 'Đã tự động điền các môn học. Hãy kiểm tra lại kỹ trước khi lưu.',
                            confirmButtonText: 'Đã hiểu'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Thất bại',
                            text: 'Không thể phân tích được dữ liệu từ ảnh. Hãy thử ảnh rõ nét hơn.'
                        });
                    }
                },
                error: function(xhr) {
                    let message = 'Đã xảy ra lỗi khi gọi AI.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: message
                    });
                },
                complete: function() {
                    aiImageInput.val('');
                }
            });
        };
        reader.readAsDataURL(file);
    }

    function applyTimetableData(data) {
        // Clear all select inputs first
        $('.timetable-grid select').val('').trigger('change');

        for (let weekday in data) {
            for (let period in data[weekday]) {
                const subjectId = data[weekday][period];
                const select = $(`select[name="timetable[${weekday}][${period}]"]`);
                
                if (select.length && subjectId) {
                    select.val(subjectId).trigger('change');
                }
            }
        }
    }
});
</script>
@endpush
@endsection

