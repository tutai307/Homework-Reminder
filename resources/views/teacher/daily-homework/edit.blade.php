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

                <h6 class="mb-3">Bài tập theo tiết học:</h6>
                
                <div class="row g-3 mb-4">
                    @foreach($timetables as $index => $timetable)
                        @php
                            $subject = $timetable->subject;
                            $period = $timetable->period;
                            $existingItem = $homework->items->where('subject_id', $subject->id)->first();
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="period-box card h-100" data-period="{{ $period }}" data-subject-id="{{ $subject->id }}" data-bs-toggle="collapse" data-bs-target="#period-{{ $period }}-{{ $subject->id }}" role="button" aria-expanded="false">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">
                                            <span class="badge bg-primary me-2">Tiết {{ $period }}</span>
                                            <strong>{{ $subject->name }}</strong>
                                        </h6>
                                        <i class="bi bi-chevron-down toggle-icon"></i>
                                    </div>
                                    
                                    <div class="collapse" id="period-{{ $period }}-{{ $subject->id }}">
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="mb-3">
                                                <label for="homework_{{ $period }}_{{ $subject->id }}_content" class="form-label small">
                                                    Nội dung bài tập <span class="text-muted">(để trống nếu không có)</span>
                                                </label>
                                                <textarea name="homework[{{ $index }}][content]" 
                                                          id="homework_{{ $period }}_{{ $subject->id }}_content" 
                                                          class="form-control @error('homework.'.$index.'.content') is-invalid @enderror" 
                                                          rows="3" 
                                                          placeholder="Nhập nội dung bài tập...">{{ old('homework.'.$index.'.content', $existingItem->content ?? '') }}</textarea>
                                                @error('homework.'.$index.'.content')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="homework_{{ $period }}_{{ $subject->id }}_due_date" class="form-label small">
                                                    Hạn nộp (tùy chọn)
                                                </label>
                                                <input type="date" 
                                                       name="homework[{{ $index }}][due_date]" 
                                                       id="homework_{{ $period }}_{{ $subject->id }}_due_date" 
                                                       class="form-control @error('homework.'.$index.'.due_date') is-invalid @enderror" 
                                                       value="{{ old('homework.'.$index.'.due_date', $existingItem ? ($existingItem->due_date ? $existingItem->due_date->format('Y-m-d') : '') : '') }}">
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
    
    .period-box[aria-expanded="true"] .toggle-icon {
        transform: rotate(180deg);
    }
    
    .period-box .collapse {
        pointer-events: none;
    }
    
    .period-box .collapse.show {
        pointer-events: auto;
    }
    
    .collapse.show {
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
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
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-expand boxes that have existing content
        document.querySelectorAll('.period-box').forEach(box => {
            const textarea = box.querySelector('textarea');
            if (textarea && textarea.value.trim() !== '') {
                const collapseId = box.querySelector('.collapse').id;
                const collapseElement = document.getElementById(collapseId);
                const bsCollapse = new bootstrap.Collapse(collapseElement, {
                    toggle: true
                });
                box.setAttribute('aria-expanded', 'true');
            }
        });
        
        // Ngăn chặn click vào form elements bên trong dropdown trigger collapse
        document.querySelectorAll('.period-box .collapse').forEach(collapse => {
            collapse.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    });
</script>
@endpush
@endsection
