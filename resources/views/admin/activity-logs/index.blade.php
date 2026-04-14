@extends('layouts.app')

@section('title', 'Nhật ký Hoạt động (Audit Log)')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="bi bi-list-columns-reverse me-2 text-primary"></i>Nhật ký Hệ thống
            </h1>
            <p class="text-muted mt-2 mb-0">Theo dõi chi tiết các thao tác của Giáo viên và Ban cán sự.</p>
        </div>
        <button class="btn btn-outline-primary" onclick="window.location.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i>Làm mới
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.activity-logs.index') }}" method="GET" class="row gx-3 gy-2 align-items-center">
                <div class="col-sm-3">
                    <label class="visually-hidden" for="startDate">Từ ngày</label>
                    <input type="text" class="form-control datepicker" id="startDate" name="start_date" placeholder="Từ ngày" value="{{ request('start_date') }}">
                </div>
                <div class="col-sm-3">
                    <label class="visually-hidden" for="endDate">Đến ngày</label>
                    <input type="text" class="form-control datepicker" id="endDate" name="end_date" placeholder="Đến ngày" value="{{ request('end_date') }}">
                </div>
                <div class="col-sm-4">
                    <label class="visually-hidden" for="search">Tìm kiếm</label>
                    <div class="input-group">
                        <div class="input-group-text bg-white"><i class="bi bi-search"></i></div>
                        <input type="text" class="form-control border-start-0 ps-0" id="search" name="search" placeholder="Tên user, email hoặc hành động..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary px-4">Lọc</button>
                    @if(request()->hasAny(['start_date', 'end_date', 'search']))
                        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-light text-muted ms-1"><i class="bi bi-x-circle"></i> Xóa</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Timeline & Data -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Thời gian</th>
                            <th>Mô tả hành động</th>
                            <th>Tài khoản</th>
                            <th>Nhóm Quyền</th>
                            <th>Lớp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 text-nowrap">
                                <span class="fw-medium text-dark">{{ $log->created_at->format('H:i') }}</span>
                                <span class="text-muted small ms-1">{{ $log->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td>
                                @if($log->action === 'create_homework')
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 me-2"><i class="bi bi-journal-plus me-1"></i>Giao bài</span>
                                @elseif($log->action === 'view_homework')
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 me-2"><i class="bi bi-eye me-1"></i>Xem bài</span>
                                @elseif($log->action === 'update_homework')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 me-2"><i class="bi bi-pencil-square me-1"></i>Sửa bài</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 me-2"><i class="bi bi-activity me-1"></i>Khác</span>
                                @endif
                                <span class="text-muted">{{ $log->description }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle-sm bg-primary bg-opacity-10 me-2 text-primary fw-bold small">
                                        {{ substr($log->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $log->user->name }}</div>
                                        <div class="text-muted small">{{ $log->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($log->user->isTeacher())
                                    <span class="badge bg-success">Giáo viên</span>
                                @elseif($log->user->isClassMonitor())
                                    <span class="badge bg-info text-dark">Lớp trưởng</span>
                                @elseif($log->user->isAcademicSubMonitor())
                                    <span class="badge bg-info text-dark">Lớp phó</span>
                                @elseif($log->user->isAdmin())
                                    <span class="badge bg-danger">Admin</span>
                                @else
                                    <span class="badge bg-secondary">{{ $log->user->role }}</span>
                                @endif
                            </td>
                            <td>
                                @if($log->class)
                                    <span class="fw-medium text-primary">{{ $log->class->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-inbox text-gray-300 fs-1 d-block mb-3"></i>
                                <h6 class="text-muted">Chưa có dữ liệu nhật ký nào.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
