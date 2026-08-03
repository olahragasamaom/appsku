<?php

namespace App\Http\Controllers\EmployeePortal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $company = $user->company;
        $employee = Employee::with(['faceEmbedding', 'company'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $company->today())
            ->first();

        $attendanceHistory = Attendance::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->paginate(15);

        // Face recognition data
        $faceRecognitionEnabled = $employee->company->enable_face_recognition ?? false;
        $hasFaceEnrolled = $employee->faceEmbedding && $employee->faceEmbedding->is_active;

        return view('portal.attendance', compact(
            'employee',
            'todayAttendance',
            'attendanceHistory',
            'faceRecognitionEnabled',
            'hasFaceEnrolled'
        ));
    }

    public function clockIn(): RedirectResponse
    {
        $user = auth()->user();
        $company = $user->company;
        $employee = Employee::with(['workSchedule', 'weeklySchedules.workSchedule'])->where('user_id', $user->id)->firstOrFail();
        $today = $company->today();

        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->hasClockedIn()) {
            return redirect()->route('portal.attendance.index')
                ->with('error', 'Anda sudah melakukan clock in hari ini.');
        }

        $workSchedule = $employee->resolveScheduleForDate(\Carbon\Carbon::parse($today));

        if ($existingAttendance) {
            $attendance = $existingAttendance;
        } else {
            $attendance = new Attendance([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'work_schedule_id' => $workSchedule?->id,
                'date' => $today,
            ]);

            if ($workSchedule) {
                $attendance->scheduled_start = $workSchedule->start_time;
                $attendance->scheduled_end = $workSchedule->end_time;
            }

            $attendance->save();
        }

        $attendance->clockIn([
            'ip' => request()->ip(),
        ]);

        return redirect()->route('portal.attendance.index')
            ->with('success', 'Clock in berhasil dicatat.');
    }

    public function clockOut(): RedirectResponse
    {
        $user = auth()->user();
        $company = $user->company;
        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $company->today())
            ->first();

        if (! $attendance || ! $attendance->hasClockedIn()) {
            return redirect()->route('portal.attendance.index')
                ->with('error', 'Anda belum melakukan clock in hari ini.');
        }

        if ($attendance->hasClockedOut()) {
            return redirect()->route('portal.attendance.index')
                ->with('error', 'Anda sudah melakukan clock out hari ini.');
        }

        $attendance->clockOut([
            'ip' => request()->ip(),
        ]);

        return redirect()->route('portal.attendance.index')
            ->with('success', 'Clock out berhasil dicatat.');
    }

    public function faceRecognitionStatus(): JsonResponse
    {
        $user = auth()->user();
        $employee = Employee::with('faceEmbedding')
            ->where('user_id', $user->id)
            ->firstOrFail();

        $embedding = $employee->faceEmbedding;
        $isEnrolled = $embedding && $embedding->is_active;

        return response()->json([
            'enrolled' => $isEnrolled,
            'enrolled_at' => $isEnrolled ? $embedding->enrolled_at?->toIso8601String() : null,
        ]);
    }
}
