@extends('layouts.app')

@section('title', 'Bảng điều khiển')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-speedometer2 me-2 text-primary"></i>Bảng điều khiển
        </h1>
        <p class="text-muted mb-0 mt-2">Tổng quan tình hình giao bài tập về nhà - Ngày {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}</p>
    </div>
</div>

<!-- Thẻ thống kê -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Tổng số lớp</h6>
                        <h3 class="mb-0 fw-bold">{{ $totalClasses }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Đã tạo bài tập</h6>
                        <h3 class="mb-0 fw-bold text-success">{{ $classesWithHomeworkCount }}</h3>
                        <small class="text-muted">lớp</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Chưa tạo bài tập</h6>
                        <h3 class="mb-0 fw-bold text-danger">{{ $classesWithoutHomeworkCount }}</h3>
                        <small class="text-muted">lớp</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-journal-text text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Tổng bài tập hôm nay</h6>
                        <h3 class="mb-0 fw-bold text-info">{{ $totalHomeworkItemsToday }}</h3>
                        <small class="text-muted">bài tập</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bảng tình trạng bài tập của lớp -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-table me-2 text-primary"></i>Bảng tình trạng bài tập về nhà của lớp
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">STT</th>
                                <th>Tên lớp</th>
                                <th>Năm học</th>
                                <th class="text-center">Đã tạo bài tập</th>
                                <th class="text-center">Số môn đã giao</th>
                                <th class="text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classesStatus as $class)
                                <tr class="{{ !$class['has_homework'] ? 'table-warning' : '' }}">
                                    <td><span class="badge bg-secondary">{{ $loop->iteration }}</span></td>
                                    <td>
                                        <strong class="{{ !$class['has_homework'] ? 'text-danger' : 'text-primary' }}">
                                            {{ $class['name'] }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">{{ $class['school_year'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($class['has_homework'])
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Có
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle me-1"></i>Không
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $class['subject_count'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if(!$class['has_homework'])
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Chưa có bài tập
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Đã hoàn thành
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bài tập theo thời khóa biểu -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-week me-2 text-primary"></i>Bài tập về nhà theo thời khóa biểu
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Tổng số môn học dự kiến</h6>
                                <h2 class="mb-0 fw-bold text-primary">{{ $totalExpectedSubjects }}</h2>
                                <small class="text-muted">môn học</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Tổng số môn đã tạo bài tập</h6>
                                <h2 class="mb-0 fw-bold text-success">{{ $totalSubjectsWithHomework }}</h2>
                                <small class="text-muted">môn học</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Tỷ lệ bao phủ</h6>
                                <h2 class="mb-0 fw-bold {{ $coverageRate >= 80 ? 'text-success' : ($coverageRate >= 50 ? 'text-warning' : 'text-danger') }}">
                                    {{ $coverageRate }}%
                                </h2>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar {{ $coverageRate >= 80 ? 'bg-success' : ($coverageRate >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                         role="progressbar" 
                                         style="width: {{ $coverageRate }}%"
                                         aria-valuenow="{{ $coverageRate }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Lưu ý:</strong> Tỷ lệ bao phủ được tính bằng số môn đã tạo bài tập chia cho tổng số môn học dự kiến trong thời khóa biểu hôm nay.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

