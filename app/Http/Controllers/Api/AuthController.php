<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendMailOtp;
use App\Models\Team;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function requestOtp(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email'  => 'required',
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

        $user = User::where('email', $request->email)->first();
        $otp = rand(100000, 999999);
        if ($user) {
            if ($user->status < 404) {
                $respon = [
                    'error' => false,
                    'status_code' => 200,
                    'message' => 'User sudah terdaftar',
                ];
                return response()->json($respon, 200);
            }
            try {
                DB::beginTransaction();

                $user->update([
                    'kode_otp' => Hash::make($otp),
                ]);

                Mail::send('email.otp', ['user' => $user, 'otp' => $otp], function ($message) use ($request) {
                    $message->from('absensi@gmail.com', 'Absensi');
                    $message->to($request->email);
                    $message->subject('Verifikasi OTP');
                });

                DB::commit();
                $respon = [
                    'error' => false,
                    'status_code' => 200,
                    'kode_otp' => $otp,
                    'message' => 'Kode OTP berhasil dikirim',
                ];
                return response()->json($respon, 200);
            } catch (\Throwable $th) {
                DB::rollBack();
                $respon = [
                    'error' => true,
                    'status_code' => 400,
                    'message' => 'Maaf, Terjadi kesalahan',
                    'dev_message' => $th->getMessage(),
                ];
                return response()->json($respon, 400);
            }
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'status' => 404,
                'kode_otp' => Hash::make($otp),
                'password' => Hash::make('G78iG77g'),
            ]);

            $team = Team::find(1);
            $team->users()->attach($user, ['role' => 'member']);
            $user->roles()->attach('0feb7d3a-90c0-42b9-be3f-63757088cb9a');

            // Mail::to($request->email)->send(new SendMailOtp($user));
            Mail::send('email.otp', ['user' => $user, 'otp' => $otp], function ($message) use ($request) {
                $message->from('absensi@gmail.com', 'Absensi');
                $message->to($request->email);
                $message->subject('Verifikasi OTP');
            });
            DB::commit();
            $respon = [
                'error' => false,
                'status_code' => 200,
                'otp' => $otp,
                'message' => 'Kode OTP berhasil dikirim',
            ];
            return response()->json($respon, 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            $respon = [
                'error' => true,
                'status_code' => 400,
                'message' => 'Maaf, Terjadi kesalahan',
                'dev_message' => $th->getMessage(),
            ];
            return response()->json($respon, 400);
        }
    }

    //validate otp
    public function validateOtp(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email'  => 'required',
            'otp' => 'required',
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

        $user = User::where('email', $request->email)->first();
        if ($user) {
            if ($user->status < 404) {
                $respon = [
                    'error' => false,
                    'status_code' => 400,
                    'message' => 'User sudah terdaftar',
                ];
                return response()->json($respon, 400);
            }
            if (!Hash::check($request->otp, $user->kode_otp)) {
                $respon = [
                    'error' => true,
                    'status_code' => 404,
                    'message' => 'Kode OTP Tidak Valid',
                ];
                return response()->json($respon, 404);
            } else {
                $respon = [
                    'error' => false,
                    'status_code' => 200,
                    'message' => 'Kode Benar',
                ];
                return response()->json($respon, 200);
            }
        }
        $respon = [
            'error' => true,
            'status_code' => 404,
            'message' => 'User tidak ditemukan',
        ];
        return response()->json($respon, 404);
    }

    // Login
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        $validate = Validator::make($request->all(), [
            'email'  => 'required',
            'password' => 'required',
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

        if (!$user) {
            $respon = [
                'error' => true,
                'status_code' => 401,
                'message' => 'Maaf, email yang Anda gunakan tidak terdaftar',
            ];
            return response()->json($respon, 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            $respon = [
                'error' => true,
                'status_code' => 401,
                'message' => 'Maaf, kata sandi yang Anda gunakan salah',
            ];
            return response()->json($respon, 401);
        }

        if ($user->status == 0) {
            $respon = [
                'error' => true,
                'status_code' => 401,
                'message' => 'Mohon maaf akun anda belum aktif',
            ];
            return response()->json($respon, 401);
        }

        $tokenResult = $user->createToken('token-auth')->plainTextToken;
        $respon = [
            'error' => false,
            'status_code' => 200,
            'message' => 'Selamat! Anda berhasil masuk aplikasi',
            'data' => [
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ]
        ];
        return response()->json($respon, 200);
    }

    public function register(Request $request)
    {
        // form validation
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'kode_otp' => 'required',
            'tanggal_lahir' => 'required',
            'tanggal_masuk_kerja' => 'required',
            'jenis_kelamin' => 'required',
            'alamat' => 'required',
            'foto_ktp' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'foto_wajah' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'cabang_id' => 'required',
            'divisi_id' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validate->fails()) {
            $respon = [
                'status' => 'error',
                'message' => 'Periksa Inputan Kembali',
                'data' => $validate->errors()
            ];

            return response()->json($respon, 400);
        }

        // validate photo ktp
        if (!$request->hasFile('foto_ktp')) {
            return response()->json([
                'error' => true,
                'message' => 'Foto KTP not found',
                'status_code' => 400,
            ], 400);
        }
        $file = $request->file('foto_ktp');
        if (!$file->isValid()) {
            return response()->json([
                'error' => true,
                'message' => 'Foto KTP not valid',
                'status_code' => 400,
            ], 400);
        }

        // validate photo wajah
        if (!$request->hasFile('foto_wajah')) {
            return response()->json([
                'error' => true,
                'message' => 'Foto Wajah not found',
                'status_code' => 400,
            ], 400);
        }
        $file = $request->file('foto_wajah');
        if (!$file->isValid()) {
            return response()->json([
                'error' => true,
                'message' => 'Foto Wajah not valid',
                'status_code' => 400,
            ], 400);
        }

        $user = User::whereEmail($request->email)->first();
        if ($user) {
            if ($user->status < 404) {
                $respon = [
                    'error' => false,
                    'status_code' => 200,
                    'message' => 'User sudah terdaftar, silahkan login',
                ];
                return response()->json($respon, 200);
            }
        }

        if (!Hash::check($request->kode_otp, $user->kode_otp)) {
            $respon = [
                'status' => 'error',
                'message' => 'Kode Otp Tidak Sesuai',
                'data' => []
            ];

            return response()->json($respon, 400);
        }
        try {
            DB::beginTransaction();
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'kode_otp' => null,
                'status' => 0,
            ];
            $user->update($userData);
            UserProfile::create([
                'tanggal_lahir' => $request->tanggal_lahir,
                'tanggal_masuk_kerja' => $request->tanggal_masuk_kerja,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat' => $request->alamat,
                'foto_ktp' => $request->foto_ktp,
                'foto_wajah' => $request->foto_wajah,
                'cabang_id' => $request->cabang_id,
                'divisi_id' => $request->divisi_id,
                'user_id' => $user->id,
                'status' => 1
            ]);


            DB::commit();
            $respon = [
                'status' => 'success',
                'message' => 'Register User Berhasil',
                // 'data' => $user
            ];

            return response()->json($respon, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            $respon = [
                'status' => 'error',
                'message' => 'Register User Gagal, Silahkan Ulangi Kembali',
                'data' => null,
                'debug' => $th->getMessage()
            ];

            return response()->json($respon, 400);
        }
    }
}
