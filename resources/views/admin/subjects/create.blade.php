@extends('layouts.app')

@section('title', 'Thêm môn học mới')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-book me-2 text-primary"></i>Thêm môn học mới
        </h1>
        <p class="text-muted mb-0 mt-2">Đăng ký một môn học mới vào hệ thống</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-info-circle me-2"></i>Thông tin môn học</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.subjects.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            <i class="bi bi-tag me-1 text-primary"></i>Tên môn học <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="VD: Toán, Ngữ văn, Tiếng Anh..."
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="code" class="form-label fw-semibold">
                            <i class="bi bi-hash me-1 text-primary"></i>Mã môn học <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('code') is-invalid @enderror" 
                               id="code" 
                               name="code" 
                               value="{{ old('code') }}" 
                               placeholder="VD: TOAN, VAN, ANH..."
                               required>
                        <div class="form-text mt-2"><i class="bi bi-exclamation-circle me-1"></i>Mã môn học phải là duy nhất và không chứa khoảng trắng.</div>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top mt-4">
                        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left me-2"></i>Hủy
                        </a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-save me-2"></i>Lưu môn học
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

