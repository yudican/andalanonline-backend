<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\ScheduleShift;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Console\Command;

class InsertScheduleAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $shifts = ScheduleShift::all();

        $users = User::whereHas('roles', function ($query) {
            return $query->where('role_type', 'member');
        })->where('status', 1)->get();

        $data = [];
        foreach ($users as $key => $user) {
            foreach ($shifts as $key => $shift) {
                $attendance = Attendance::whereDate('created_at', date('Y-m-d'))->where('user_id', $user->id)->where('schedule_shift_id', $shift->id)->first();
                if (!$attendance) {
                    if ($shift->schedule_type == 'checkin') {
                        $data[] = [
                            'user_id' => $user->id,
                            'schedule_shift_id' => $shift->id,
                            'attendance_date' => null,
                            'attendance_status' => 0,
                            'attendance_request_status' => 0,
                            'attendance_photo' => null,
                            'attendance_note' => null,
                            'attendance_active' => true,
                            'attendance_day'  => date('Y-m-d'),
                            'attendance_shift'  => $shift->shift_id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    } else {
                        $data[] = [
                            'user_id' => $user->id,
                            'schedule_shift_id' => $shift->id,
                            'attendance_date' => null,
                            'attendance_status' => 0,
                            'attendance_request_status' => 0,
                            'attendance_photo' => null,
                            'attendance_note' => null,
                            'attendance_active' => false,
                            'attendance_day'  => date('Y-m-d'),
                            'attendance_shift'  => $shift->shift_id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }
            }
        }

        Attendance::insert($data);

        // update attendance
        $attendances = Attendance::where('attendance_active', true)->get();
        foreach ($attendances as $key => $attendance) {
            $end_date = date('Y-m-d 23:59:58', strtotime($attendance->created_at));
            if ($attendance->schedule->schedule_type == 'checkout') {
                if (strtotime(date('Y-m-d H:i:s')) > strtotime($end_date)) {
                    $attendance->update(['attendance_active' => false, 'attendance_status' => 1, 'attendance_date' => date('Y-m-d H:i:s')]);
                }
            } else {
                if (strtotime(date('Y-m-d H:i:s')) > strtotime($end_date)) {
                    $attendance->update(['attendance_active' => false, 'attendance_status' => 6]);
                }
            }
        }
    }
}
