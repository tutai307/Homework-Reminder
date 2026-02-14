@extends('layouts.app')

@section('title', 'Quản lý lớp học')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-people-fill me-2 text-primary"></i>Quản lý lớp học
            </h1>
            <p class="text-muted mb-0 mt-2">Đăng ký và quản lý các lớp học trong hệ thống</p>
        </div>
        <a href="{{ route('admin.classes.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-2"></i>Thêm lớp học mới
        </a>
    </div>
</div>

@if($classes->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 80px;">STT</th>
                            <th>Thông tin lớp học</th>
                            <th>Năm học</th>
                            <th>Mô tả</th>
                            <th>Ngày cập nhật</th>
                            <th class="text-center pe-4" style="width: 150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classes as $class)
                            <tr>
                                <td class="ps-4">
                                    <span class="text-muted fw-medium">{{ $loop->iteration + ($classes->currentPage() - 1) * $classes->perPage() }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle-sm bg-primary bg-opacity-10 me-3">
                                            <i class="bi bi-building text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $class->name }}</div>
                                            <div class="small text-muted text-uppercase">Lớp học chính quy</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-soft-info text-info border border-info border-opacity-25 px-3 py-2">
                                        <i class="bi bi-calendar-event me-1"></i>{{ $class->school_year }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small" title="{{ $class->description }}">
                                        {{ Str::limit($class->description ?? 'Không có mô tả', 40) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <i class="bi bi-clock-history me-1"></i>{{ $class->updated_at->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-sm btn-outline-primary rounded-circle shadow-sm" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Xóa">
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
            
            @if($classes->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $classes->links() }}
                </div>
            @endif
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="rounded-circle bg-light d-inline-flex p-4 mb-4">
                <i class="bi bi-building-exclamation display-4 text-muted"></i>
            </div>
            <h4 class="text-muted">Chưa có lớp học nào</h4>
            <p class="text-muted">Bạn cần khởi tạo lớp học trước khi có thể gán học sinh vào hệ thống.</p>
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary mt-3 px-4 shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Thêm lớp học đầu tiên
            </a>
        </div>
    </div>
@endif

<style>
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
</style>
@endsection

