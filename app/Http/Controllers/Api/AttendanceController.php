<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\ScheduleShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{

    public function getAttendance($schedule_id)
    {
        $schedule =  Attendance::whereDoesntHave('schedule', function ($query) use ($schedule_id) {
            $query->where('schedule_shift_id', '!=', $schedule_id);
        })->where('user_id', auth()->user()->id)->get();

        $respon = [
            'status' => 'success',
            'message' => 'Get Attendance Berhasil',
            'data' => $schedule
        ];

        return response()->json($respon, 200);
    }

    public function saveAttendance(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'schedule_shift_id'  => 'required',
            'attendance_photo' => 'required',
        ]);

        if ($validate->fails()) {
            $respon = [
                'error' => true,
                'status_code' => 401,
                'message' => 'Maaf, Silahkan isi semua form yang tersedia',
                'messages' => $validate->errors(),
            ];
            return response()->json($respon, 401);
        }

        if (!$request->hasFile('attendance_photo')) {
            return response()->json([
                'error' => true,
                'message' => 'File not found',
                'status_code' => 400,
            ], 400);
        }
        $file = $request->file('attendance_photo');
        if (!$file->isValid()) {
            return response()->json([
                'error' => true,
                'message' => 'Image file not valid',
                'status_code' => 400,
            ], 400);
        }

        $file = $request->attendance_photo->store('upload', 'public');

        $schedule = ScheduleShift::find($request->schedule_shift_id);
        $now = date('Y-m-d H:i:s');
        $time_absen = date('Y-m-d ') . $schedule->schedule_time;
        $status_absen = 1;
        $minutes_late = 0;
        if (in_array($schedule->schedule_type, ['checkin', 'breakin'])) {
            if (strtotime($now) > strtotime($time_absen)) {
                $status_absen = 5;
                $minutes_late = (strtotime($now) - strtotime($time_absen)) / 60;
            }
        }
        $data = [
            'user_id'  => auth()->user()->id,
            'schedule_shift_id'  => $request->schedule_shift_id,
            'attendance_date'  => $now,
            'attendance_photo'  => $file,
            'attendance_status'  => $status_absen,
            'attendance_request_status'  => 1,
            'attendance_note'  => $minutes_late > 0 ? 'Telat (' . round($minutes_late) . ' min)' : null,
            'attendance_active'  => false,
            'attendance_day'  => date('Y-m-d'),
            'attendance_shift'  => $schedule->shift_id,
        ];

        $condition = [
            'id' => $request->attendance_id,
            'user_id'  => auth()->user()->id,
            'schedule_shift_id'  => $request->schedule_shift_id,
        ];

        $attendance = Attendance::updateOrCreate($condition, $data);

        if (in_array($schedule->schedule_type, ['checkin'])) {
            Attendance::where('user_id', auth()->user()->id)->whereHas('schedule', function ($query) use ($request) {
                $query->where('schedule_shift_id', '!=', $request->schedule_shift_id)->where('shift_id', $request->shift_id)->whereIn('schedule_type', ['breakout', 'checkout']);
            })->update(['attendance_active' => true]);
        } else if (in_array($schedule->schedule_type, ['breakout'])) {
            Attendance::whereHas('schedule', function ($query) use ($request) {
                $query->where('parent_id', $request->schedule_shift_id);
            })->update(['attendance_active' => true]);
        }

        Attendance::where('user_id', auth()->user()->id)->where('attendance_day', date('Y-m-d'))->whereNotIn('attendance_shift', [$schedule->shift_id])->delete();

        $respon = [
            'status' => 'success',
            'message' => 'Absen Berhasil',
            'data' => $attendance
        ];
        return response()->json($respon, 200);
    }

    // history
    public function attendanceHistory(Request $request)
    {
        $date = $request->date ? $request->date : date('Y-m-d');
        $attendances = Attendance::with('schedule')->whereDate('created_at', $date)->whereHas('schedule', function ($query) use ($request) {
            $query->where('shift_id', $request->shift_id);
        })->where('user_id', auth()->user()->id)->get();

        $respon = [
            'status' => 'success',
            'message' => 'Get Attendance History Berhasil',
            'data' => $attendances
        ];

        return response()->json($respon, 200);
    }

    public function reportAttendance(Request $request)
    {
        $start = $request->start_date ? $request->start_date : date('Y-m-d');
        $end = $request->end_date ? $request->end_date : date('Y-m-d', strtotime(Carbon::now()->addDays(30)));
        $attendances = Attendance::whereBetween('created_at', [$start, $end])->where('user_id', auth()->user()->id)->groupBy('attendance_day')->get();

        $date = date('Y-m-01 H:i:s');
        $date_now = date('Y-m-d H:i:s');
        $date_of_expiry = date('Y-m-t H:i:s');
        $total = strtotime($date_of_expiry) - strtotime($date);
        $diff = strtotime($date_of_expiry) - strtotime($date_now);
        $percent = floor($diff / (60 * 60 * 24));
        $total = floor($total / (60 * 60 * 24));

        $telat = 0;
        $sakit = 0;
        $izin = 0;
        $cuti = 0;
        $alpha = 0;
        $overbreak = 0;
        foreach ($attendances as $key => $attendance) {
            if ($attendance->schedule->schedule_type == 'checkin' && $attendance->attendance_status == 5) {
                $telat += 1;
            } else if ($attendance->schedule->schedule_type == 'breakin' && $attendance->attendance_status == 5) {
                $overbreak += 1;
            } else if ($attendance->attendance_status == 2) {
                $sakit += 1;
            } else if ($attendance->attendance_status == 3) {
                $izin += 1;
            } else if ($attendance->attendance_status == 4) {
                $cuti += 1;
            } else if ($attendance->attendance_status == 0) {
                $alpha += 1;
            }
        }

        $attendances = Attendance::with('schedule')->whereBetween('created_at', [$start, $end])->where('user_id', auth()->user()->id)->get();


        $respon = [
            'status' => 'success',
            'message' => 'Get Attendance History Berhasil',
            'data' => [
                'title' => 'Laporan ' . date('d F Y', strtotime($start)) . ' - ' . date('d F Y', strtotime($end)),
                'percent' => round(($total - $percent) / $total * 100),
                'list' => $attendances,
                'result' => [
                    'telat' => $telat,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'cuti' => $cuti,
                    'alpha' => $alpha,
                    'overbreak' => $overbreak,
                ]
            ]
        ];

        return response()->json($respon, 200);
    }

    public function attendanceRequest(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'request_type'  => 'required',
            'request_date' => 'required',
            'request_shift' => 'required',
        ]);

        if ($validate->fails()) {
            $respon = [
                'error' => true,
                'status_code' => 401,
                'message' => 'Maaf, Silahkan isi semua form yang tersedia',
                'messages' => $validate->errors(),
            ];
            return response()->json($respon, 401);
        }

        $data = [
            'request_type' => $request->request_type,
            'request_description' => $request->request_description,
            'request_date' => $request->request_date,
            'request_end_date' => $request->request_end_date,
            'request_status' => $request->request_status,
            'shift_id' => $request->request_shift,
            'user_id' => auth()->user()->id,
        ];

        if ($request->request_photo) {
            if (!$request->hasFile('request_photo')) {
                return response()->json([
                    'error' => true,
                    'message' => 'File not found',
                    'status_code' => 400,
                ], 400);
            }
            $file = $request->file('request_photo');
            if (!$file->isValid()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Image file not valid',
                    'status_code' => 400,
                ], 400);
            }

            $file = $request->request_photo->store('upload', 'public');
            $data['request_photo'] = $file;
        }

        $attendance = AttendanceRequest::create($data);

        $respon = [
            'status' => 'success',
            'message' => 'Data Berhasil Disimpan',
            'data' => $attendance
        ];

        return response()->json($respon, 200);
    }

    public function getAttendanceRequest()
    {
        $attendances = AttendanceRequest::where('user_id', auth()->user()->id)->orderBy('created_at', 'DESC')->get();

        $respon = [
            'status' => 'success',
            'message' => 'Data Berhasil Disimpan',
            'data' => $attendances
        ];

        return response()->json($respon, 200);
    }
}
