@extends('layouts.app')

@section('title', 'Quản lý thời khóa biểu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Quản lý thời khóa biểu</h1>
</div>

@if($classes->count() > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Chọn lớp để quản lý thời khóa biểu</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($classes as $class)
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $class->name }}</h5>
                                <p class="card-text text-muted mb-2">
                                    <small>Năm học: {{ $class->school_year }}</small>
                                </p>
                                @if($class->description)
                                    <p class="card-text">
                                        <small>{{ Str::limit($class->description, 50) }}</small>
                                    </p>
                                @endif
                                <a href="{{ route('teacher.timetables.create', $class) }}" class="btn btn-primary btn-sm">
                                    Quản lý thời khóa biểu
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@else
    <div class="alert alert-info">
        <p class="mb-0">Chưa có lớp học nào. Vui lòng tạo lớp học trước.</p>
    </div>
@endif
@endsection

