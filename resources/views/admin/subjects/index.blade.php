@extends('layouts.app')

@section('title', 'Quản lý môn học')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-book me-2 text-primary"></i>Quản lý môn học
            </h1>
            <p class="text-muted mb-0 mt-2">Danh sách các môn học được giảng dạy tại trường</p>
        </div>
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-2"></i>Thêm môn học mới
        </a>
    </div>
</div>

@if($subjects->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 80px;">STT</th>
                            <th>Thông tin môn học</th>
                            <th>Mã định danh</th>
                            <th>Ngày cập nhật</th>
                            <th class="text-center pe-4" style="width: 150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects as $subject)
                            <tr>
                                <td class="ps-4">
                                    <span class="text-muted fw-medium">{{ $loop->iteration + ($subjects->currentPage() - 1) * $subjects->perPage() }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle-sm bg-primary bg-opacity-10 me-3">
                                            <i class="bi bi-journal-check text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $subject->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border border-primary border-opacity-25 px-3 py-2">
                                        <i class="bi bi-hash me-1"></i>{{ $subject->code }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small text-muted">
                                        <i class="bi bi-clock-history me-1"></i>{{ $subject->updated_at->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-primary rounded-circle shadow-sm" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="d-inline delete-form">
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
            
            @if($subjects->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $subjects->links() }}
                </div>
            @endif
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="rounded-circle bg-light d-inline-flex p-4 mb-4">
                <i class="bi bi-journal-x display-4 text-muted"></i>
            </div>
            <h4 class="text-muted">Chưa có môn học nào</h4>
            <p class="text-muted">Hệ thống cần ít nhất một môn học để gán cho giáo viên và bài tập.</p>
            <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary mt-3 px-4 shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Thêm môn học đầu tiên
            </a>
        </div>
    </div>
@endif
@endsection

