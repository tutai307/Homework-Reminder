@foreach($classesStatus as $class)
<tr style="cursor: pointer;" onclick="viewClassHomework({{ $class['id'] }}, '{{ $selectedDate }}')" class="hover-shadow-sm transition-all">
    <td class="ps-4">
        <div class="d-flex align-items-center">
            <div class="icon-circle-sm bg-primary bg-opacity-10 me-2">
                <span class="text-primary fw-bold small">{{ substr($class['name'], 0, 2) }}</span>
            </div>
            <span class="fw-bold">{{ $class['name'] }}</span>
        </div>
    </td>
    <td>{{ $class['school_year'] }}</td>
    <td class="text-center">
        <span class="badge bg-light text-dark fw-normal border">
            {{ $class['subject_count'] }}/{{ $class['expected_count'] }} môn
        </span>
    </td>
    <td class="text-center" style="width: 250px;">
        <div class="d-flex align-items-center">
            <div class="progress flex-grow-1" style="height: 8px;">
                <div class="progress-bar bg-primary shadow-sm" role="progressbar" 
                    style="width: {{ $class['coverage_rate'] }}%" 
                    aria-valuenow="{{ $class['coverage_rate'] }}" 
                    aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <span class="ms-2 small fw-bold text-muted" style="width: 40px;">{{ $class['coverage_rate'] }}%</span>
        </div>
    </td>
    <td class="text-end pe-4">
        @if($class['coverage_rate'] == 100)
            <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                <i class="bi bi-check-all me-1"></i>Hoàn thành
            </span>
        @elseif($class['has_homework'])
            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3">
                <i class="bi bi-check me-1"></i>Đã giao
            </span>
        @else
            <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3">
                <i class="bi bi-clock me-1"></i>Đang chờ
            </span>
        @endif
    </td>
</tr>
@endforeach
