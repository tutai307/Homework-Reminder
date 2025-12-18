<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\HomeworkItem;
use App\Models\Timetable;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClassPortalController extends Controller
{
    /**
     * Public page for parents/students: timetable + upcoming homework by due_date.
     */
    public function show(Request $request, string $code)
    {
        $class = ClassModel::where('public_share_slug', $code)
            ->orWhere('public_share_token', $code)
            ->firstOrFail();
        $class->ensurePublicShareSlug();
        $class->ensurePublicShareToken();

        $now = now();
        $tomorrow = $now->copy()->addDay()->startOfDay();
        $dayAfterTomorrow = $now->copy()->addDays(2)->startOfDay();

        $timetables = Timetable::where('class_id', $class->id)
            ->with('subject')
            ->orderBy('weekday')
            ->orderBy('period')
            ->get();

        $timetablesByWeekday = $timetables->groupBy('weekday');

        // Homework items to do: based on due_date (tomorrow & day after tomorrow)
        $dueDates = [
            $tomorrow->format('Y-m-d'),
            $dayAfterTomorrow->format('Y-m-d'),
        ];

        $upcomingItems = HomeworkItem::whereHas('homework', function ($query) use ($class) {
                $query->where('class_id', $class->id);
            })
            ->whereIn('due_date', $dueDates)
            ->with(['subject'])
            ->orderBy('due_date')
            ->orderBy('subject_id')
            ->get();

        $itemsByDueDate = $upcomingItems->groupBy(function ($item) {
            return $item->due_date ? $item->due_date->format('Y-m-d') : 'no_due_date';
        });

        $weekdayNames = [
            1 => 'Thứ 2',
            2 => 'Thứ 3',
            3 => 'Thứ 4',
            4 => 'Thứ 5',
            5 => 'Thứ 6',
            6 => 'Thứ 7',
            7 => 'CN',
        ];

        return view('public.class-portal', [
            'class' => $class,
            'now' => $now,
            'tomorrow' => $tomorrow,
            'dayAfterTomorrow' => $dayAfterTomorrow,
            'timetablesByWeekday' => $timetablesByWeekday,
            'itemsByDueDate' => $itemsByDueDate,
            'weekdayNames' => $weekdayNames,
        ]);
    }
}


