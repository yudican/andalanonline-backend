<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScheduleShift;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedule = ScheduleShift::where('shift_id', auth()->user()->shift_id)->get();
        $respon = [
            'status' => 'success',
            'message' => 'Get Schedule Berhasil',
            'data' => $schedule
        ];

        return response()->json($respon, 200);
    }
}
