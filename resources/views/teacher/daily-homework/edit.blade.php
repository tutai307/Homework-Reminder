@extends('layouts.app')

@section('title', 'Chỉnh sửa bài tập - ' . $class->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Chỉnh sửa bài tập hàng ngày</h1>
        <p class="text-muted mb-0">
            Lớp: <strong>{{ $class->name }}</strong> - 
            Ngày: <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong>
            ({{ \Carbon\Carbon::parse($date)->locale('vi')->dayName }})
        </p>
    </div>
    <a href="{{ route('teacher.daily-homework.index') }}" class="btn btn-secondary">
        Quay lại
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Chỉnh sửa bài tập cho các tiết học</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('teacher.daily-homework.update', $homework) }}" method="POST" id="homework-form">
            @csrf
            @method('PUT')

            @if($timetables->count() > 0)
                <div class="mb-3">
                    <label for="notes" class="form-label">Ghi chú chung (tùy chọn)</label>
                    <textarea name="notes" 
                              id="notes" 
                              class="form-control" 
                              rows="2" 
                              placeholder="Nhập ghi chú chung nếu có...">{{ old('notes', $homework->notes) }}</textarea>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Bài tập theo tiết học:</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#aiParseModal">
                        <i class="bi bi-robot me-1"></i>AI Parse (Nhập nhanh)
                    </button>
                </div>
                
                <div class="row g-3 mb-4">
                    @foreach($timetables as $index => $group)
                        @php
                            $subject = $group->subject;
                            $existingItem = $homework->items->where('subject_id', $subject->id)->first();
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="period-box card h-100" data-subject-id="{{ $subject->id }}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2 period-header" data-bs-toggle="collapse" data-bs-target="#subject-{{ $subject->id }}" role="button" aria-expanded="false" style="cursor: pointer;">
                                        <h6 class="mb-0">
                                            <span class="badge bg-primary me-2">{{ $group->periods_display }}</span>
                                            <strong>{{ $subject->name }}</strong>
                                        </h6>
                                        <i class="bi bi-chevron-down toggle-icon"></i>
                                    </div>
                                    
                                    <div class="collapse" id="subject-{{ $subject->id }}">
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="mb-3">
                                                <label for="homework_{{ $subject->id }}_content" class="form-label small">
                                                    Nội dung bài tập <span class="text-muted">(để trống nếu không có)</span>
                                                </label>
                                                <textarea name="homework[{{ $index }}][content]" 
                                                          id="homework_{{ $subject->id }}_content" 
                                                          class="form-control @error('homework.'.$index.'.content') is-invalid @enderror" 
                                                          rows="3" 
                                                          placeholder="Nhập nội dung bài tập...">{{ old('homework.'.$index.'.content', $existingItem ? $existingItem->content : '') }}</textarea>
                                                @error('homework.'.$index.'.content')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="homework_{{ $subject->id }}_due_date" class="form-label small">
                                                    Hạn nộp (tùy chọn)
                                                </label>
                                                <input type="text" 
                                                       name="homework[{{ $index }}][due_date]" 
                                                       id="homework_{{ $subject->id }}_due_date" 
                                                       class="form-control datepicker @error('homework.'.$index.'.due_date') is-invalid @enderror" 
                                                       value="{{ old('homework.'.$index.'.due_date', $existingItem ? ($existingItem->due_date ? $existingItem->due_date->format('Y-m-d') : '') : '') }}"
                                                       placeholder="Chọn ngày hạn nộp">
                                                @error('homework.'.$index.'.due_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <input type="hidden" name="homework[{{ $index }}][subject_id]" value="{{ $subject->id }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('teacher.daily-homework.index') }}" class="btn btn-secondary">
                        Hủy
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Cập nhật bài tập
                    </button>
                </div>
            @else
                <div class="alert alert-warning">
                    <p class="mb-0">
                        Không có môn học nào trong thời khóa biểu cho ngày này.
                    </p>
                </div>
                <a href="{{ route('teacher.daily-homework.index') }}" class="btn btn-secondary">
                    Quay lại
                </a>
            @endif
        </form>
    </div>
</div>

<!-- AI Parse Modal -->
<div class="modal fade" id="aiParseModal" tabindex="-1" aria-labelledby="aiParseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aiParseModalLabel">
                    <i class="bi bi-robot me-2"></i>AI Parse Homework
                </h5>
                <button type="button" class="btn-close" data-bs-close="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>Bạn có thể nhập đoạn văn bản tự do chứa nội dung bài tập. AI sẽ tự động phân tách vào các môn học tương ứng trong thời khóa biểu hôm nay.
                    <br>
                    <small>Ví dụ: "Toán làm bài 1,2 trang 45. Văn soạn bài 'Bếp lửa'."</small>
                </div>
                <div class="mb-3">
                    <label for="ai-homework-text" class="form-label">Nội dung bài tập (Văn bản tự do):</label>
                    <textarea class="form-control" id="ai-homework-text" rows="6" placeholder="Nhập nội dung tại đây..."></textarea>
                </div>
                <div id="ai-parse-results" class="d-none mt-3">
                    <h6><i class="bi bi-eye me-2"></i>Kết quả dự kiến:</h6>
                    <div class="list-group" id="ai-results-list">
                        <!-- Results will be populated here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btn-start-ai-parse">
                    <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                    <i class="bi bi-magic me-1"></i>Bắt đầu Parse
                </button>
                <button type="button" class="btn btn-success d-none" id="btn-confirm-ai-parse">
                    <i class="bi bi-check-circle me-1"></i>Xác nhận & Điền vào Form
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .period-box {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
    }
    
    .period-box:hover {
        border-color: #0d6efd;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transform: translateY(-2px);
    }
    
    .period-box .card-body {
        min-height: 100px;
    }
    
    .toggle-icon {
        transition: transform 0.3s ease;
        color: #6c757d;
    }
    
    .period-header[aria-expanded="true"] .toggle-icon {
        transform: rotate(180deg);
    }
    
    .period-header {
        user-select: none;
    }
    
    /* Let Bootstrap handle the collapse animation to avoid a one-frame "blink"
       caused by applying opacity:0 when the `.show` class is added. */
    .period-box .collapse,
    .period-box .collapsing {
        will-change: height;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-expand boxes that have existing content
        document.querySelectorAll('.period-box').forEach(box => {
            const textarea = box.querySelector('textarea');
            if (textarea && textarea.value.trim() !== '') {
                const collapseId = box.querySelector('.collapse').id;
                const collapseElement = document.getElementById(collapseId);
                const header = box.querySelector('.period-header');
                const bsCollapse = new bootstrap.Collapse(collapseElement, {
                    toggle: true
                });
                if (header) {
                    header.setAttribute('aria-expanded', 'true');
                }
            }
        });
        
        // Cập nhật aria-expanded khi collapse mở/đóng
        document.querySelectorAll('.period-box .collapse').forEach(collapse => {
            collapse.addEventListener('show.bs.collapse', function() {
                const header = this.closest('.period-box').querySelector('.period-header');
                if (header) {
                    header.setAttribute('aria-expanded', 'true');
                }
            });
            
            collapse.addEventListener('hide.bs.collapse', function() {
                const header = this.closest('.period-box').querySelector('.period-header');
                if (header) {
                    header.setAttribute('aria-expanded', 'false');
                }
            });
        });

        // AI Parse Logic
        const btnStartParse = document.getElementById('btn-start-ai-parse');
        const btnConfirmParse = document.getElementById('btn-confirm-ai-parse');
        const aiTextarea = document.getElementById('ai-homework-text');
        const resultsDiv = document.getElementById('ai-parse-results');
        const resultsList = document.getElementById('ai-results-list');
        let parsedData = [];

        // Lấy danh sách môn học hợp lệ từ timetable UI
        const validSubjects = [];
        document.querySelectorAll('.period-box').forEach(box => {
            const subjectName = box.querySelector('strong').innerText.trim();
            const subjectId = box.dataset.subjectId;
            validSubjects.push({ id: subjectId, name: subjectName });
        });

        const normalizeVietnamese = (str) => {
            if (!str) return '';
            str = str.toLowerCase();
            const map = {
                'hoá': 'hóa', 'hoà': 'hòa', 'hoả': 'hỏa', 'hoã': 'hóa', 'hoạ': 'họa',
                'oá': 'óa', 'oà': 'òa', 'oả': 'ỏa', 'oã': 'óa', 'oạ': 'ọa',
                'uý': 'úy', 'uỳ': 'ủy', 'uỷ': 'ủy', 'uỹ': 'úy', 'uỵ': 'ụy'
            };
            for (let key in map) {
                str = str.replace(new RegExp(key, 'g'), map[key]);
            }
            return str;
        };

        const aliasMap = {
            'Toán': ['toán học', 't'],
            'Toán học': ['toán', 't'],
            'Ngữ văn': ['văn', 'tiếng việt', 'nv'],
            'Tiếng Anh': ['av', 'anh', 't.anh', 'english', 'nn1', 'ngoại ngữ'],
            'Khoa học tự nhiên': ['khtn', 'tự nhiên', 'lý-hóa-sinh', 'lý hóa sinh', 'lý', 'hóa', 'sinh'],
            'Lịch sử và Địa lý': ['ls-đl', 'sử-địa', 'sử địa', 'sử', 'địa', 'lsđl'],
            'Giáo dục công dân': ['gdcd', 'công dân', 'đạo đức'],
            'Tin học': ['tin', 'th'],
            'Công nghệ': ['cn', 'kỹ thuật'],
            'Giáo dục thể chất': ['gdtc', 'thể dục', 'td'],
            'Nghệ thuật': ['âm nhạc', 'mĩ thuật', 'vẽ', 'nhạc', 'nt', 'mỹ thuật'],
            'Âm nhạc': ['nhạc', 'an'],
            'Mĩ thuật': ['vẽ', 'mt', 'mỹ thuật'],
            'Hoạt động trải nghiệm, hướng nghiệp': ['hđtn', 'trải nghiệm', 'hướng nghiệp', 'tnhn'],
            'Hoạt động trải nghiệm': ['hđtn', 'trải nghiệm', 'tnhn'],
            'Giáo dục địa phương': ['gdđp', 'địa phương'],
            'Ngoại ngữ 2': ['nn2', 'tiếng nhật', 'tiếng trung', 'tiếng pháp', 'tiếng hàn'],
        };

        btnStartParse.addEventListener('click', function() {
            const text = aiTextarea.value.trim();
            if (!text) {
                Swal.fire('Lỗi', 'Vui lòng nhập văn bản bài tập.', 'error');
                return;
            }

            // Frontend Validation: Kiểm tra xem có chứa môn học nào hợp lệ không (bao gồm cả viết tắt)
            const normalizedInput = normalizeVietnamese(text);
            const found = validSubjects.some(s => {
                const normalizedOfficial = normalizeVietnamese(s.name);
                if (normalizedInput.includes(normalizedOfficial)) return true;

                // Check aliases
                const aliases = aliasMap[s.name] || [];
                return aliases.some(alias => normalizedInput.includes(normalizeVietnamese(alias)));
            });
            if (!found) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Không hợp lệ',
                    text: 'Văn bản của bạn không chứa môn học nào có trong thời khóa biểu hôm nay. Vui lòng kiểm tra lại.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            // Tiến hành gọi API
            btnStartParse.disabled = true;
            btnStartParse.querySelector('.spinner-border').classList.remove('d-none');
            
            $.ajax({
                url: "{{ route('teacher.ai-homework.parse') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    text: text,
                    class_id: "{{ $class->id }}",
                    date: "{{ $homework->date->format('Y-m-d') }}"
                },
                success: function(response) {
                    if (response.success) {
                        parsedData = response.data;
                        displayResults(parsedData);
                    } else {
                        Swal.fire('Lỗi', response.message || 'Không thể parse dữ liệu.', 'error');
                    }
                },
                error: function(xhr) {
                    let msg = 'Đã xảy ra lỗi khi gọi AI. Vui lòng thử lại.';
                    if (xhr.status === 422) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Lỗi', msg, 'error');
                },
                complete: function() {
                    btnStartParse.disabled = false;
                    btnStartParse.querySelector('.spinner-border').classList.add('d-none');
                }
            });
        });

        function displayResults(data) {
            resultsList.innerHTML = '';
            data.forEach(item => {
                let deadlineBadge = '';
                if (item.due_date) {
                    // Chuyển YYYY-MM-DD sang DD/MM/YYYY cho UI
                    const parts = item.due_date.split('-');
                    const formattedDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
                    deadlineBadge = `<span class="badge bg-warning text-dark"><i class="bi bi-calendar-event me-1"></i>Hạn nộp: ${formattedDate}</span>`;
                }

                const html = `
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 text-primary">${item.subject_name}</h6>
                            ${deadlineBadge}
                        </div>
                        <p class="mb-0 small text-muted">${item.content}</p>
                    </div>
                `;
                resultsList.insertAdjacentHTML('beforeend', html);
            });
            resultsDiv.classList.remove('d-none');
            btnConfirmParse.classList.remove('d-none');
        }

        btnConfirmParse.addEventListener('click', function() {
            parsedData.forEach(item => {
                // Tìm textarea và input date tương ứng với môn học
                const box = document.querySelector(`.period-box[data-subject-id="${item.subject_id}"]`);
                if (box) {
                    const textarea = box.querySelector('textarea');
                    const dateInput = box.querySelector('input[type="text"].datepicker');

                    if (textarea) {
                        textarea.value = item.content;
                    }

                    if (dateInput && item.due_date) {
                        if (dateInput._flatpickr) {
                            dateInput._flatpickr.setDate(item.due_date);
                        } else {
                            // Fallback: Nếu vì lý do nào đó Flatpickr chưa init, format lại để hiển thị
                            const parts = item.due_date.split('-');
                            if (parts.length === 3) {
                                dateInput.value = `${parts[2]}/${parts[1]}/${parts[0]}`;
                            } else {
                                dateInput.value = item.due_date;
                            }
                        }
                    }

                    // Mở box nếu đang đóng
                    const collapseId = box.querySelector('.collapse').id;
                    const collapseElement = document.getElementById(collapseId);
                    if (!collapseElement.classList.contains('show')) {
                        const bsCollapse = new bootstrap.Collapse(collapseElement, { toggle: true });
                        box.querySelector('.period-header').setAttribute('aria-expanded', 'true');
                    }
                }
            });

            // Đóng modal và reset
            bootstrap.Modal.getInstance(document.getElementById('aiParseModal')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Đã điền bài tập!',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
            
            // Reset modal state
            aiTextarea.value = '';
            resultsDiv.classList.add('d-none');
            btnConfirmParse.classList.add('d-none');
        });
    });
</script>
@endpush
@endsection
