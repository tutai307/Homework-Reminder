@extends('layouts.app')

@section('title', 'Chỉnh sửa lớp học')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-pencil-square me-2 text-primary"></i>Chỉnh sửa lớp học
        </h1>
        <p class="text-muted mb-0 mt-2">Cập nhật thông tin lớp học</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin lớp học</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.classes.update', $class) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            <i class="bi bi-tag me-1 text-primary"></i>Tên lớp <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $class->name) }}" 
                               placeholder="VD: 10A1, 11B2..."
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="school_year" class="form-label fw-semibold">
                            <i class="bi bi-calendar-range me-1 text-primary"></i>Năm học <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('school_year') is-invalid @enderror" 
                               id="school_year" 
                               name="school_year" 
                               value="{{ old('school_year', $class->school_year) }}" 
                               placeholder="VD: 2024-2025"
                               required>
                        @error('school_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">
                            <i class="bi bi-card-text me-1 text-primary"></i>Mô tả
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="4"
                                  placeholder="Nhập mô tả về lớp học (tùy chọn)...">{{ old('description', $class->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

