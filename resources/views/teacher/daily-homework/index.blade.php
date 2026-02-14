@extends('layouts.app')

@section('title', 'Tạo bài tập hàng ngày')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-journal-text me-2 text-primary"></i>Bài tập hàng ngày
            </h1>
            <p class="text-muted mb-0 mt-2">Chọn lớp để quản lý và giao bài tập</p>
        </div>
        <div>
            <a href="{{ route('teacher.daily-homework.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Tạo bài tập nhanh
            </a>
        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="class-grid">
    @foreach($classes as $class)
        <div class="col">
            <div class="card h-100 class-card border-0 shadow-sm" onclick="window.location.href='{{ route('teacher.daily-homework.list', ['class_id' => $class->id, 'date' => date('Y-m-d')]) }}'">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="class-icon bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">{{ $class->name }}</h5>
                            <small class="text-muted">Năm học: {{ $class->school_year }}</small>
                        </div>
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <span class="badge bg-light text-primary border">
                            <i class="bi bi-calendar-check me-1"></i>Hôm nay
                        </span>
                        <a href="{{ route('teacher.daily-homework.list', ['class_id' => $class->id, 'date' => date('Y-m-d')]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                            Giao bài <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('styles')
<style>
    .class-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 15px;
    }
    .class-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .class-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .page-title {
        font-weight: 700;
        color: #334155;
    }
</style>
@endpush
@endsection

