@extends('layouts.app')

@section('title', 'Tạo bài tập hàng ngày')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-journal-text me-2 text-primary"></i>Bài tập hàng ngày
            </h1>
            <p class="text-muted mb-0 mt-2">Quản lý và xem bài tập theo ngày</p>
        </div>
        <div>
            <a href="{{ route('teacher.daily-homework.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Tạo bài tập mới
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Chọn lớp để xem bài tập</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('teacher.daily-homework.list') }}" method="GET" id="classForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="class_id" class="form-label fw-semibold">Chọn lớp <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id" class="form-select form-select-lg @error('class_id') is-invalid @enderror" required>
                        <option value="">-- Chọn lớp --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} - {{ $class->school_year }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-search me-2"></i>Xem bài tập
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

