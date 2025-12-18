@extends('layouts.app')

@section('title', 'Thông tin lớp - ' . $class->name)

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h1 class="page-title">
                <i class="bi bi-people-fill me-2 text-primary"></i>{{ $class->name }}
                @php
                    $dow = $now->dayOfWeekIso; // 1=Mon ... 7=Sun
                    $dowLabel = $dow === 7 ? 'CN' : 'Thứ ' . ($dow + 1);
                @endphp
                <span class="badge bg-primary-subtle text-primary align-middle ms-2">
                    {{ $dowLabel }}
                </span>
            </h1>
            <p class="text-muted mb-0 mt-2">
                Năm học: <strong>{{ $class->school_year }}</strong>
                <span class="mx-2">•</span>
                Cập nhật: <strong><span id="client-clock">{{ $now->format('H:i:s') }}</span></strong>
                <span class="text-muted">({{ $now->format('d/m/Y') }})</span>
            </p>
            @if(!empty($class->description))
                <p class="text-muted mb-0 mt-1">{{ $class->description }}</p>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-calendar-week me-2"></i>Thời khóa biểu</span>
            </div>
            <div class="card-body">
                @php
                    $maxPeriod = $timetablesByWeekday->flatten()->max('period') ?? 0;
                @endphp
                @if($maxPeriod === 0)
                    <div class="alert alert-light border mb-0">
                        <i class="bi bi-info-circle me-2 text-muted"></i>Chưa có thời khóa biểu cho lớp này.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start">Tiết</th>
                                    @foreach($weekdayNames as $weekday => $weekdayLabel)
                                        <th>{{ $weekdayLabel }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @for($p = 1; $p <= $maxPeriod; $p++)
                                    <tr>
                                        <th class="text-start">Tiết {{ $p }}</th>
                                        @foreach($weekdayNames as $weekday => $weekdayLabel)
                                            @php
                                                $slot = optional($timetablesByWeekday->get($weekday, collect())->firstWhere('period', $p));
                                            @endphp
                                            <td>
                                                @if($slot)
                                                    <div class="fw-normal">{{ $slot->subject?->name }}</div>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                        @endforeach

                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-journal-text me-2"></i>Bài tập cần làm</span>
            </div>
            <div class="card-body">
                @php
                    $tomorrowKey = $tomorrow->format('Y-m-d');
                    $dayAfterKey = $dayAfterTomorrow->format('Y-m-d');
                    $itemsTomorrow = $itemsByDueDate->get($tomorrowKey, collect());
                    $itemsDayAfter = $itemsByDueDate->get($dayAfterKey, collect());
                @endphp

                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">
                            <span class="badge bg-info text-dark me-2">Ngày mai</span>
                            <strong>{{ $tomorrow->format('d/m/Y') }}</strong>
                        </h6>
                        <span class="text-muted small">{{ $itemsTomorrow->count() }} bài</span>
                    </div>

                    @if($itemsTomorrow->count() > 0)
                        <div class="list-group">
                            @foreach($itemsTomorrow as $item)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold">
                                                <i class="bi bi-book me-2 text-primary"></i>{{ $item->subject->name }}
                                            </div>
                                            <div class="text-muted mt-1" style="white-space: pre-wrap;">{{ $item->content }}</div>
                                        </div>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-calendar-event me-1"></i>Hạn
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">
                            <i class="bi bi-check-circle me-2 text-success"></i>Chưa có bài tập nào hạn vào ngày mai.
                        </div>
                    @endif
                </div>

                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">
                            <span class="badge bg-secondary me-2">Ngày kế tiếp</span>
                            <strong>{{ $dayAfterTomorrow->format('d/m/Y') }}</strong>
                        </h6>
                        <span class="text-muted small">{{ $itemsDayAfter->count() }} bài</span>
                    </div>

                    @if($itemsDayAfter->count() > 0)
                        <div class="list-group">
                            @foreach($itemsDayAfter as $item)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold">
                                                <i class="bi bi-book me-2 text-primary"></i>{{ $item->subject->name }}
                                            </div>
                                            <div class="text-muted mt-1" style="white-space: pre-wrap;">{{ $item->content }}</div>
                                        </div>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-calendar-event me-1"></i>Hạn
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">
                            <i class="bi bi-info-circle me-2 text-muted"></i>Chưa có bài tập nào hạn vào ngày kế tiếp.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Simple client-side clock so the page feels "live" without reload
    (function () {
        const el = document.getElementById('client-clock');
        if (!el) return;
        const pad = (n) => String(n).padStart(2, '0');
        const tick = () => {
            const d = new Date();
            el.textContent = `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
        };
        tick();
        setInterval(tick, 1000);
    })();
</script>
@endpush


