<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Master\DivisiResource;
use App\Models\Divisi;
use Illuminate\Http\Request;

class DivisiController extends Controller
{
    public function index()
    {
        $divisis = Divisi::all();

        $respon = [
            'status' => 'success',
            'message' => 'Get Divisi Berhasil',
            'data' => DivisiResource::collection($divisis)
        ];

        return response()->json($respon, 200);
    }
}
