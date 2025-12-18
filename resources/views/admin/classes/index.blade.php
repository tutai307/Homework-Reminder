@extends('layouts.app')

@section('title', 'Quản lý lớp học')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-people-fill me-2 text-primary"></i>Quản lý lớp học
            </h1>
            <p class="text-muted mb-0 mt-2">Quản lý danh sách các lớp học trong hệ thống</p>
        </div>
        <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Thêm lớp học mới
        </a>
    </div>
</div>

@if($classes->count() > 0)
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Tên lớp</th>
                            <th>Năm học</th>
                            <th>Mô tả</th>
                            <th style="width: 120px;">Ngày tạo</th>
                            <th style="width: 150px;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classes as $class)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $loop->iteration }}</span></td>
                                <td>
                                    <strong class="text-primary">{{ $class->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $class->school_year }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ Str::limit($class->description ?? 'Không có mô tả', 50) }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $class->created_at->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-sm btn-outline-primary" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" class="d-inline delete-form" onsubmit="return false;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger delete-btn" title="Xóa">
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
            
            <div class="card-footer bg-white border-top">
                {{ $classes->links() }}
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="mt-3 text-muted">Chưa có lớp học nào</h4>
            <p class="text-muted">Bắt đầu bằng cách thêm lớp học đầu tiên</p>
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>Thêm lớp học đầu tiên
            </a>
        </div>
    </div>
@endif
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                
                Swal.fire({
                    title: 'Bạn có chắc chắn?',
                    text: "Bạn có muốn xóa lớp học này không? Hành động này không thể hoàn tác!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash me-1"></i>Có, xóa!',
                    cancelButtonText: 'Hủy',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection

