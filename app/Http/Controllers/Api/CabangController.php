<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Master\CabangResource;
use App\Models\Cabang;
use Illuminate\Http\Request;

class CabangController extends Controller
{
  public function index()
  {
    $cabangs = Cabang::all();

    $respon = [
      'status' => 'success',
      'message' => 'Get Cabang Berhasil',
      'data' => CabangResource::collection($cabangs)
    ];

    return response()->json($respon, 200);
  }
}
