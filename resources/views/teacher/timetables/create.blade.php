@extends('layouts.app')

@section('title', 'Thời khóa biểu - ' . $class->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Thời khóa biểu</h1>
        <p class="text-muted mb-0">Lớp: <strong>{{ $class->name }}</strong> - Năm học: {{ $class->school_year }}</p>
    </div>
    <a href="{{ route('teacher.timetables.index') }}" class="btn btn-secondary">
        Quay lại
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Gán môn học cho các ngày trong tuần</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('teacher.timetables.store', $class) }}" method="POST">
            @csrf
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Tiết</th>
                            <th>Thứ 2</th>
                            <th>Thứ 3</th>
                            <th>Thứ 4</th>
                            <th>Thứ 5</th>
                            <th>Thứ 6</th>
                            <th>Thứ 7</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($period = 1; $period <= 10; $period++)
                            <tr>
                                <td class="fw-bold">Tiết {{ $period }}</td>
                                @for($weekday = 1; $weekday <= 6; $weekday++)
                                    <td>
                                        <select name="timetable[{{ $weekday }}][{{ $period }}]" 
                                                class="form-select form-select-sm">
                                            <option value="">-- Chọn môn --</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}"
                                                    @if(isset($timetables[$weekday][$period]) && $timetables[$weekday][$period] == $subject->id) selected @endif>
                                                    {{ $subject->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endfor
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-between">
                <a href="{{ route('teacher.timetables.index') }}" class="btn btn-secondary">
                    Hủy
                </a>
                <button type="submit" class="btn btn-primary">
                    Lưu thời khóa biểu
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .table select {
        min-width: 120px;
    }
</style>
@endpush
@endsection

