@extends('layouts.app')

@section('title', 'Quản lý thời khóa biểu')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-calendar3 me-2 text-primary"></i>Quản lý thời khóa biểu
            </h1>
            <p class="text-muted mb-0 mt-2">Chọn một lớp học để thiết lập hoặc chỉnh sửa thời khóa biểu hàng tuần</p>
        </div>
    </div>
</div>

@if($classes->count() > 0)
    <div class="row">
        @foreach($classes as $class)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100 class-card hover-shadow transition">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-3">
                            <div class="icon-circle bg-primary bg-opacity-10 me-3">
                                <i class="bi bi-building text-primary fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-1">{{ $class->name }}</h5>
                                <span class="badge bg-soft-info text-info border border-info border-opacity-25 px-2 py-1 small">
                                    <i class="bi bi-calendar-event me-1"></i>{{ $class->school_year }}
                                </span>
                            </div>
                        </div>
                        
                        @if($class->description)
                            <p class="text-muted small mb-4">
                                {{ Str::limit($class->description, 80) }}
                            </p>
                        @else
                            <p class="text-muted small mb-4 italic">Không có mô tả cho lớp học này.</p>
                        @endif

                        <div class="mt-auto">
                            <a href="{{ route('teacher.timetables.create', $class) }}" class="btn btn-primary w-100 shadow-sm">
                                <i class="bi bi-table me-2"></i>Quản lý thời khóa biểu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="rounded-circle bg-light d-inline-flex p-4 mb-4">
                <i class="bi bi-calendar-x display-4 text-muted"></i>
            </div>
            <h4 class="text-muted">Chưa có lớp học nào</h4>
            <p class="text-muted">Bạn cần được gán vào ít nhất một lớp học để quản lý thời khóa biểu.</p>
        </div>
    </div>
@endif

<style>
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .transition { transition: all 0.3s ease; }
</style>
@endsection

