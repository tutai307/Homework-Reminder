@extends('layouts.app')

@section('title', 'Chỉnh sửa môn học')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-pencil-square me-2 text-primary"></i>Chỉnh sửa môn học
        </h1>
        <p class="text-muted mb-0 mt-2">Cập nhật thông tin cho môn học: <strong>{{ $subject->name }}</strong></p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-info-circle me-2"></i>Thông tin môn học</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.subjects.update', $subject) }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            <i class="bi bi-tag me-1 text-primary"></i>Tên môn học <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $subject->name) }}" 
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
                               value="{{ old('code', $subject->code) }}" 
                               placeholder="VD: TOAN, VAN, ANH..."
                               required>
                        <div class="form-text mt-2"><i class="bi bi-exclamation-circle me-1"></i>Hạn chế thay đổi mã môn học nếu đã có dữ liệu liên quan.</div>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top mt-4">
                        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left me-2"></i>Hủy
                        </a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-check-circle me-2"></i>Cập nhật môn học
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

