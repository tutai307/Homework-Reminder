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
        <a href="{{ route('teacher.timetables.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-arrow-left me-1"></i>Quay lại
        </a>
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
                                    <td class="p-2">
                                        @php
                                            $oldValue = old("timetable.$weekday.$period", $timetables[$weekday][$period] ?? '');
                                        @endphp
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
    .timetable-grid td { transition: background-color 0.2s; }
    .timetable-grid td:hover { background-color: rgba(13, 110, 253, 0.02); }
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    
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
@endsection

