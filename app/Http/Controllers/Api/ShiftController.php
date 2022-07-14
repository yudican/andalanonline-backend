<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::with('scheduleShift.attendance')->get();

        $respon = [
            'status' => 'success',
            'message' => 'Get Shifts Berhasil',
            'data' => $shifts
        ];

        return response()->json($respon, 200);
    }
}
