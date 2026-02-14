@extends('layouts.app')

@section('title', 'Quản lý ban cán sự')

@section('content')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-people-fill me-2 text-primary"></i>Quản lý Ban cán sự
            </h1>
            <p class="text-muted mb-0 mt-2">Quản lý đội ngũ nòng cốt cho lớp: <strong>{{ $class->name }}</strong></p>
        </div>
        @if($classes->count() > 1)
            <a href="{{ route('teacher.class-monitor.index') }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-2"></i>Chọn lớp khác
            </a>
        @endif
    </div>

<div class="row">
    <!-- Thẻ Lớp trưởng -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-badge me-2"></i>Lớp trưởng</h5>
                @if(!$classMonitor)
                    <a href="{{ route('teacher.class-monitor.create', ['role' => 'class_monitor', 'class_id' => $class->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-plus-lg me-1"></i>Thêm mới
                    </a>
                @endif
            </div>
            <div class="card-body">
                @if($classMonitor)
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-circle-lg bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-person-check fs-2"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $classMonitor->name }}</h5>
                            <p class="text-muted small mb-0">{{ $classMonitor->email }}</p>
                        </div>
                    </div>
                    <div class="bg-light p-3 rounded-3 mb-4">
                        <div class="row small text-muted">
                            <div class="col-6 mb-2">Ngày bổ nhiệm:</div>
                            <div class="col-6 mb-2 text-dark fw-semibold text-end">{{ $classMonitor->created_at->format('d/m/Y') }}</div>
                            <div class="col-6">Trạng thái:</div>
                            <div class="col-6 text-end text-success fw-bold">Đang hoạt động</div>
                        </div>
                    </div>
                    <div class="d-grid mt-auto">
                        <form action="{{ route('teacher.class-monitor.destroy', $classMonitor) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-light text-danger w-100 border-0">
                                <i class="bi bi-person-x me-2"></i>Xóa quyền lớp trưởng
                            </button>
                        </form>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="icon-circle bg-light text-muted mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-person-x fs-1"></i>
                        </div>
                        <p class="text-muted mb-0">Chưa có lớp trưởng</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Thẻ Lớp phó học tập -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-person-workspace me-2"></i>Lớp phó học tập</h5>
                @if(!$academicSubMonitor)
                    <a href="{{ route('teacher.class-monitor.create', ['role' => 'academic_sub_monitor', 'class_id' => $class->id]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-plus-lg me-1"></i>Thêm mới
                    </a>
                @endif
            </div>
            <div class="card-body">
                @if($academicSubMonitor)
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-circle-lg bg-secondary bg-opacity-10 text-secondary me-3">
                            <i class="bi bi-person-up fs-2"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $academicSubMonitor->name }}</h5>
                            <p class="text-muted small mb-0">{{ $academicSubMonitor->email }}</p>
                        </div>
                    </div>
                    <div class="bg-light p-3 rounded-3 mb-4">
                        <div class="row small text-muted">
                            <div class="col-6 mb-2">Ngày bổ nhiệm:</div>
                            <div class="col-6 mb-2 text-dark fw-semibold text-end">{{ $academicSubMonitor->created_at->format('d/m/Y') }}</div>
                            <div class="col-6">Trạng thái:</div>
                            <div class="col-6 text-end text-success fw-bold">Đang hoạt động</div>
                        </div>
                    </div>
                    <div class="d-grid mt-auto">
                        <form action="{{ route('teacher.class-monitor.destroy', $academicSubMonitor) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-light text-danger w-100 border-0">
                                <i class="bi bi-person-x me-2"></i>Xóa quyền lớp phó
                            </button>
                        </form>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="icon-circle bg-light text-muted mx-auto mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-person-dash fs-1"></i>
                        </div>
                        <p class="text-muted mb-0">Chưa có lớp phó học tập</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

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
                    text: "Bạn có muốn xóa lớp trưởng này không? Hành động này không thể hoàn tác!",
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

