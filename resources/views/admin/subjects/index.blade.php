@extends('layouts.app')

@section('title', 'Quản lý môn học')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Quản lý môn học</h1>
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Thêm môn học mới
    </a>
</div>

@if($subjects->count() > 0)
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên môn học</th>
                            <th>Mã môn</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects as $subject)
                            <tr>
                                <td>{{ $subject->id }}</td>
                                <td><strong>{{ $subject->name }}</strong></td>
                                <td><code>{{ $subject->code }}</code></td>
                                <td>{{ $subject->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-primary">
                                            Sửa
                                        </a>
                                        <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa môn học này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $subjects->links() }}
            </div>
        </div>
    </div>
@else
    <div class="alert alert-info">
        <p class="mb-0">Chưa có môn học nào. <a href="{{ route('admin.subjects.create') }}">Thêm môn học đầu tiên</a></p>
    </div>
@endif
@endsection

