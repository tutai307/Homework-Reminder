@extends('layouts.app')

@section('title', 'Quản lý vai trò')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-shield-check me-2 text-primary"></i>Quản lý vai trò
            </h1>
            <p class="text-muted mb-0 mt-2">Quản lý các vai trò và quyền trong hệ thống</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Thêm vai trò mới
        </a>
    </div>
</div>

@if($roles->count() > 0)
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">STT</th>
                            <th>Tên vai trò</th>
                            <th>Quyền</th>
                            <th style="width: 150px;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $loop->iteration }}</span></td>
                                <td><strong>{{ $role->name }}</strong></td>
                                <td>
                                    @if($role->permissions->count() > 0)
                                        @foreach($role->permissions as $permission)
                                            <span class="badge bg-info">{{ $permission->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Chưa có quyền</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline delete-form" onsubmit="return false;">
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
                {{ $roles->links() }}
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="mt-3 text-muted">Chưa có vai trò nào</h4>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>Thêm vai trò đầu tiên
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
                const formElement = this;
                
                Swal.fire({
                    title: 'Bạn có chắc chắn?',
                    text: "Bạn có muốn xóa vai trò này không?",
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

