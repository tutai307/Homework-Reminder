@extends('layouts.app')

@section('title', 'Quản lý lớp trưởng')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-person-badge me-2 text-primary"></i>Quản lý lớp trưởng
            </h1>
            <p class="text-muted mb-0 mt-2">Quản lý lớp trưởng cho lớp: <strong>{{ $class->name }}</strong></p>
        </div>
        @if(!$classMonitor)
            <a href="{{ route('teacher.class-monitor.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Tạo lớp trưởng
            </a>
        @endif
    </div>
</div>

@if($classMonitor)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin lớp trưởng</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 150px;">Tên:</th>
                            <td><strong>{{ $classMonitor->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $classMonitor->email }}</td>
                        </tr>
                        <tr>
                            <th>Vai trò:</th>
                            <td>
                                <span class="badge bg-success">Lớp trưởng</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Ngày tạo:</th>
                            <td>{{ $classMonitor->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="d-flex gap-2 pt-3 border-top">
                <form action="{{ route('teacher.class-monitor.destroy', $classMonitor) }}" method="POST" class="delete-form" onsubmit="return false;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Xóa lớp trưởng
                    </button>
                </form>
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-person-x display-1 text-muted"></i>
            <h4 class="mt-3 text-muted">Chưa có lớp trưởng</h4>
            <p class="text-muted">Lớp <strong>{{ $class->name }}</strong> chưa có lớp trưởng</p>
            <a href="{{ route('teacher.class-monitor.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i>Tạo lớp trưởng
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

