<?php

namespace App\Http\Controllers;

use App\Models\VeterinarianSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class CalendarEventController extends Controller
{
    public function index(): JsonResponse
    {
        $daysOfWeek = [
            'domingo' => 0,
            'lunes' => 1,
            'martes' => 2,
            'miércoles' => 3,
            'jueves' => 4,
            'viernes' => 5,
            'sábado' => 6,
        ];

        $events = VeterinarianSchedule::with('veterinarian.user')->get()->map(function ($schedule) use ($daysOfWeek) {
            return [
                'id' => $schedule->id,
                'title' => $schedule->veterinarian->user->name ?? 'Veterinario',
                'daysOfWeek' => [$daysOfWeek[$schedule->day_of_week] ?? 0],
                'startTime' => \Carbon\Carbon::parse($schedule->start_time)->format('H:i:s'),
                'endTime' => \Carbon\Carbon::parse($schedule->end_time)->format('H:i:s'),
                'startRecur' => '2025-01-01',
                'display' => 'auto',
            ];
        });

        return response()->json($events);
    }
}
