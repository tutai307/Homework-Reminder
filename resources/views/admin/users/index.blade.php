@extends('layouts.app')

@section('title', 'Quản lý người dùng')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-people-fill me-2 text-primary"></i>Quản lý người dùng
            </h1>
            <p class="text-muted mb-0 mt-2">Quản lý tài khoản và phân quyền người dùng</p>
        </div>
        <a href="{{ route('admin.users.create', ['class_id' => $classId]) }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-2"></i>Thêm người dùng mới
        </a>
    </div>
</div>

@if(!$classId)
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
        <!-- Card: Tất cả người dùng -->
        <div class="col">
            <div class="card h-100 class-card border-0 shadow-sm" onclick="window.location.href='{{ route('admin.users.index', ['class_id' => 'all']) }}'">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-secondary bg-opacity-10 text-secondary me-3">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">Tất cả người dùng</h5>
                            <small class="text-muted">Tổng số hệ thống</small>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end align-items-center">
                        <a href="{{ route('admin.users.index', ['class_id' => 'all']) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @foreach($classes as $class)
            <div class="col">
                <div class="card h-100 class-card border-0 shadow-sm" onclick="window.location.href='{{ route('admin.users.index', ['class_id' => $class->id]) }}'">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-mortarboard-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0 fw-bold">{{ $class->name }}</h5>
                                <small class="text-muted">Năm học: {{ $class->school_year }}</small>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-primary border">
                                <i class="bi bi-person-badge me-1"></i>{{ $class->users_count }} người dùng
                            </span>
                            <a href="{{ route('admin.users.index', ['class_id' => $class->id]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                Chi tiết <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Card: Người dùng chưa gán lớp -->
        <div class="col">
            <div class="card h-100 class-card border-0 shadow-sm" onclick="window.location.href='{{ route('admin.users.index', ['class_id' => 'none']) }}'">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-person-exclamation fs-4"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">Chưa gán lớp</h5>
                            <small class="text-muted">Cần phân bổ</small>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end align-items-center">
                        <a href="{{ route('admin.users.index', ['class_id' => 'none']) }}" class="btn btn-outline-warning btn-sm rounded-pill px-3">
                            Kiểm tra <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Quản lý người dùng</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $selectedClass->name }}</li>
                </ol>
            </nav>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Quay lại chọn lớp
            </a>
        </div>
    </div>

    @if($users->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-list-stars me-2"></i>Danh sách: {{ $selectedClass->name }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">STT</th>
                                <th>Thông tin người dùng</th>
                                <th>Vai trò hệ thống</th>
                                <th>Lớp được gán</th>
                                <th class="text-center pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td class="ps-4"><span class="text-muted">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle-sm bg-primary bg-opacity-10 me-3">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                <div class="small text-muted">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border-danger border-opacity-25 px-3">Admin</span>
                                        @elseif($user->role === 'teacher')
                                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border-primary border-opacity-25 px-3">Giáo viên</span>
                                        @elseif($user->role === 'class_monitor')
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success border-success border-opacity-25 px-3">Lớp trưởng</span>
                                        @elseif($user->role === 'academic_sub_monitor')
                                            <span class="badge rounded-pill bg-info bg-opacity-10 text-info border-info border-opacity-25 px-3">Lớp phó học tập</span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border px-3">{{ $user->role }}</span>
                                        @endif
                                        
                                        <!-- @foreach($user->roles as $role)
                                            <span class="badge bg-light text-dark border ms-1">{{ $role->name }}</span>
                                        @endforeach -->
                                    </td>
                                    <td>
                                        @if($user->classes->count() > 0)
                                            @foreach($user->classes as $class)
                                                <span class="badge bg-light text-primary border me-1">{{ $class->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted small italic">Chưa gán</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer bg-white border-top py-3">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-person-x display-1 text-muted"></i>
                <h4 class="mt-4 text-muted">Không tìm thấy người dùng nào trong lớp này</h4>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle me-2"></i>Thêm người dùng mới
                </a>
            </div>
        </div>
    @endif
@endif

@push('styles')
<style>
    .class-card {
        cursor: pointer;
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
        border-radius: 15px;
    }
    .class-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
    .breadcrumb-item a {
        text-decoration: none;
        color: #6c757d;
    }
    .breadcrumb-item.active {
        color: #0d6efd;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formElement = this;
                
                Swal.fire({
                    title: 'Bạn có chắc chắn?',
                    text: "Bạn có muốn xóa người dùng này không? Hành động này không thể hoàn tác!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash me-1"></i>Có, xóa!',
                    cancelButtonText: 'Hủy',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        formElement.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection

