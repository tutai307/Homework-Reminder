@extends('layouts.app')

@section('title', 'Chỉnh sửa người dùng')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-person-gear me-2 text-primary"></i>Chỉnh sửa người dùng
        </h1>
        <p class="text-muted mb-0 mt-2">Cập nhật thông tin và quyền người dùng</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin người dùng</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            <i class="bi bi-person me-1 text-primary"></i>Tên <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
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
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">
                            <i class="bi bi-lock me-1 text-primary"></i>Mật khẩu mới (để trống nếu không đổi)
                        </label>
                        <input type="password" 
                               class="form-control form-control-lg @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            <i class="bi bi-lock-fill me-1 text-primary"></i>Xác nhận mật khẩu mới
                        </label>
                        <input type="password" 
                               class="form-control form-control-lg" 
                               id="password_confirmation" 
                               name="password_confirmation">
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label fw-semibold">
                            <i class="bi bi-shield-check me-1 text-primary"></i>Vai trò <span class="text-danger">*</span>
                        </label>
                        <select name="role" id="role" class="form-select form-select-lg @error('role') is-invalid @enderror" required>
                            <option value="">-- Chọn vai trò --</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="teacher" {{ old('role', $user->role) == 'teacher' ? 'selected' : '' }}>Giáo viên</option>
                            <option value="class_monitor" {{ old('role', $user->role) == 'class_monitor' ? 'selected' : '' }}>Lớp trưởng</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-key me-1 text-primary"></i>Roles
                        </label>
                        <div class="row">
                            @foreach($roles as $role)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                               {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-people me-1 text-primary"></i>Gán lớp (chỉ dành cho giáo viên)
                        </label>
                        <div class="row">
                            @foreach($classes as $class)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="classes[]" value="{{ $class->id }}" id="class_{{ $class->id }}"
                                               {{ $user->classes->contains($class->id) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="class_{{ $class->id }}">
                                            {{ $class->name }} - {{ $class->school_year }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
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

