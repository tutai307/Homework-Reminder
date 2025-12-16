@extends('layouts.app')

@section('title', 'Tạo lớp trưởng')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-person-plus me-2 text-primary"></i>Tạo lớp trưởng
        </h1>
        <p class="text-muted mb-0 mt-2">Tạo tài khoản lớp trưởng cho lớp: <strong>{{ $class->name }}</strong></p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin lớp trưởng</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('teacher.class-monitor.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            <i class="bi bi-person me-1 text-primary"></i>Tên lớp trưởng <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">
                            <i class="bi bi-envelope me-1 text-primary"></i>Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control form-control-lg @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">
                            <i class="bi bi-lock me-1 text-primary"></i>Mật khẩu <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control form-control-lg @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            <i class="bi bi-lock-fill me-1 text-primary"></i>Xác nhận mật khẩu <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control form-control-lg" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Lưu ý:</strong> Lớp trưởng sẽ được tự động gán vào lớp <strong>{{ $class->name }}</strong> và có quyền tạo bài tập hàng ngày cho lớp này.
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('teacher.class-monitor.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Tạo lớp trưởng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

