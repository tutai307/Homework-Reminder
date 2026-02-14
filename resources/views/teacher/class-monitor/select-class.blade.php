@extends('layouts.app')

@section('title', 'Chọn lớp quản lý Ban cán sự')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">
                <i class="bi bi-people-fill me-2 text-primary"></i>Quản lý Ban cán sự
            </h1>
            <p class="text-muted mb-0 mt-2">Chọn lớp bạn muốn quản lý Lớp trưởng và Lớp phó học tập</p>
        </div>
    </div>
</div>

<div class="row g-4">
    @foreach($classes as $class)
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 premium-card hover-translate transition">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $class->name }}</h4>
                            <span class="text-muted small">Năm học: {{ $class->school_year }}</span>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="{{ route('teacher.class-monitor.index', ['class_id' => $class->id]) }}" class="btn btn-outline-primary rounded-pill py-2">
                            Quản lý Ban cán sự <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('styles')
<style>
    .premium-card {
        border-radius: 20px;
    }
    .hover-translate:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px -10px rgba(0,0,0,0.1) !important;
    }
    .icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
</style>
@endpush
@endsection
