@extends('layouts.app')

@section('title', 'Thêm người dùng mới')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-person-plus me-2 text-primary"></i>Thêm người dùng mới
        </h1>
        <p class="text-muted mb-0 mt-2">Tạo tài khoản mới và gán quyền</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-info-circle me-2"></i>Thông tin người dùng</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            <i class="bi bi-person me-1 text-primary"></i>Tên <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Nhập họ và tên..."
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
                               placeholder="example@gmail.com"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="bi bi-lock me-1 text-primary"></i>Mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Tối thiểu 8 ký tự"
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                <i class="bi bi-lock-fill me-1 text-primary"></i>Xác nhận mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control form-control-lg" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Nhập lại mật khẩu"
                                   required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label fw-semibold">
                            <i class="bi bi-shield-check me-1 text-primary"></i>Vai trò <span class="text-danger">*</span>
                        </label>
                        <select name="role" id="role" class="form-select select2 @error('role') is-invalid @enderror" required data-placeholder="-- Chọn vai trò --">
                            <option value=""></option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Giáo viên</option>
                            <option value="class_monitor" {{ old('role') == 'class_monitor' ? 'selected' : '' }}>Lớp trưởng</option>
                            <option value="academic_sub_monitor" {{ old('role') == 'academic_sub_monitor' ? 'selected' : '' }}>Lớp phó học tập</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4" id="class-selection-container">
                        <label for="classes" class="form-label fw-semibold">
                            <i class="bi bi-mortarboard me-1 text-primary"></i>Gán lớp 
                            <span id="class-selection-label" class="text-muted small"></span>
                        </label>
                        
                        @if($preselectedClassId)
                            <div class="alert alert-info py-2 small border-0 shadow-sm mb-3">
                                <i class="bi bi-info-circle me-2"></i>Vì bạn đang thêm người dùng từ danh sách lớp, lớp đã được gán tự động.
                            </div>
                            <input type="hidden" name="classes[]" value="{{ $preselectedClassId }}">
                            <select class="form-select select2" disabled>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ $preselectedClassId == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} ({{ $class->school_year }})
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <select name="classes[]" id="classes" class="form-select select2" multiple data-placeholder="-- Chọn lớp --">
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ (is_array(old('classes')) && in_array($class->id, old('classes'))) ? 'selected' : '' }}>
                                        {{ $class->name }} ({{ $class->school_year }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text mt-2" id="class-help-text"></div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top mt-4">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary px-4">
                            <i class="bi bi-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-check-circle me-2"></i>Lưu người dùng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $(document).ready(function() {
        const roleSelect = $('#role');
        const classContainer = $('#class-selection-container');
        const classSelect = $('#classes');
        const classLabel = $('#class-selection-label');
        const helpText = $('#class-help-text');

        function updateClassSection() {
            const role = roleSelect.val();
            
            // Reset state
            classContainer.show();
            classSelect.prop('disabled', false);
            
            if (role === 'admin') {
                classContainer.hide();
                classSelect.val(null).trigger('change');
            } else if (role === 'teacher') {
                classLabel.text('(Có thể chọn nhiều lớp)');
                classSelect.attr('multiple', 'multiple');
                helpText.text('Giáo viên có thể phụ trách nhiều lớp học.');
            } else if (role === 'class_monitor' || role === 'academic_sub_monitor') {
                classLabel.text('(Chỉ được chọn 1 lớp)');
                classSelect.removeAttr('multiple');
                helpText.text('Ban cán sự lớp chỉ được gán cho duy nhất 1 lớp.');
                
                // Nếu đang có nhiều hơn 1 value, chỉ giữ lại cái đầu tiên
                let currentVal = classSelect.val();
                if (Array.isArray(currentVal) && currentVal.length > 1) {
                    classSelect.val(currentVal[0]).trigger('change');
                }
            }
            
            // Re-initialize Select2 to apply multiple attribute changes
            classSelect.select2({
                placeholder: classSelect.data('placeholder'),
                allowClear: true,
                width: '100%'
            });
        }

        roleSelect.on('change', updateClassSection);
        
        // Chạy lần đầu nếu không phải preselected
        @if(!$preselectedClassId)
            updateClassSection();
        @endif
    });
</script>
@endpush
@endsection

