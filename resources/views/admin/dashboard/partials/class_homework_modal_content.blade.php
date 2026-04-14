@if($homework && $homework->items->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="bg-light">
                <tr>
                    <th style="width: 30%">Môn học</th>
                    <th>Nội dung dặn dò</th>
                </tr>
            </thead>
            <tbody>
                @foreach($homework->items as $item)
                    <tr>
                        <td class="fw-bold text-primary">{{ $item->subject->name ?? 'Không rõ' }}</td>
                        <td>{!! nl2br(e($item->content ?: 'Không có nội dung')) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-4">
        <i class="bi bi-inbox fs-1 text-muted"></i>
        <p class="mt-2 mb-0 text-muted">Chưa có bài tập nào được ghi nhận cho ngày này.</p>
    </div>
@endif
