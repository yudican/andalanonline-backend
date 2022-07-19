<?php

namespace App\Http\Livewire\Member;

use App\Models\Cabang;
use App\Models\Divisi;
use App\Models\Team;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;;


class UserProfileController extends Component
{
    use WithFileUploads;
    public $user_profile_id;
    public $name;
    public $email;
    public $alamat;
    public $cabang_id;
    public $divisi_id;
    public $foto_ktp;
    public $foto_wajah;
    public $jenis_kelamin;
    public $tanggal_lahir;
    public $tanggal_masuk_kerja;
    public $user_id;
    public $foto_ktp_path;
    public $foto_wajah_path;


    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataUserProfileById', 'getUserProfileId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.member.user-profiles', [
            'divisis' => Divisi::all(),
            'cabangs' => Cabang::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        // validate email
        $userExists = User::where('email', $this->email)->first();
        if ($userExists) {
            return $this->addError('email', 'User Sudah Terdaftar');
        }

        $foto_ktp = $this->foto_ktp_path->store('upload', 'public');
        $foto_wajah = $this->foto_wajah_path->store('upload', 'public');
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'status' => 1,
            'password' => Hash::make('user123'),
        ]);

        $team = Team::find(1);
        $team->users()->attach($user, ['role' => 'member']);
        $user->roles()->attach('0feb7d3a-90c0-42b9-be3f-63757088cb9a');
        $data = [

            'alamat'  => $this->alamat,
            'cabang_id'  => $this->cabang_id,
            'divisi_id'  => $this->divisi_id,
            'foto_ktp'  => $foto_ktp,
            'foto_wajah'  => $foto_wajah,
            'jenis_kelamin'  => $this->jenis_kelamin,
            'tanggal_lahir'  => $this->tanggal_lahir,
            'tanggal_masuk_kerja'  => $this->tanggal_masuk_kerja,
            'user_id'  => $user->id
        ];

        UserProfile::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'alamat'  => $this->alamat,
            'cabang_id'  => $this->cabang_id,
            'divisi_id'  => $this->divisi_id,
            'jenis_kelamin'  => $this->jenis_kelamin,
            'tanggal_lahir'  => $this->tanggal_lahir,
            'tanggal_masuk_kerja'  => $this->tanggal_masuk_kerja,
        ];

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
        ];
        $user = User::find($this->user_id);
        $row = UserProfile::find($this->user_profile_id);


        if ($this->foto_ktp_path) {
            $foto_ktp = $this->foto_ktp_path->store('upload', 'public');
            $data = ['foto_ktp' => $foto_ktp];
            if (Storage::exists('public/' . $this->foto_ktp)) {
                Storage::delete('public/' . $this->foto_ktp);
            }
        }

        if ($this->foto_wajah_path) {
            $foto_wajah = $this->foto_wajah_path->store('upload', 'public');
            $data = ['foto_wajah' => $foto_wajah];
            if (Storage::exists('public/' . $this->foto_wajah)) {
                Storage::delete('public/' . $this->foto_wajah);
            }
        }

        $row->update($data);
        $user->update($userData);
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        $user = User::find($this->user_id);
        if ($user) {
            $user->delete();
        }

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'email'  => 'required|email',
            'alamat'  => 'required',
            'cabang_id'  => 'required',
            'divisi_id'  => 'required',
            'jenis_kelamin'  => 'required',
            'tanggal_lahir'  => 'required',
            'tanggal_masuk_kerja'  => 'required',
        ];

        return $this->validate($rule);
    }

    public function getDataUserProfileById($user_profile_id)
    {
        $this->_reset();
        $row = UserProfile::find($user_profile_id);
        $this->user_profile_id = $row->id;
        $this->name = $row->user->name;
        $this->email = $row->user->email;
        $this->alamat = $row->alamat;
        $this->cabang_id = $row->cabang_id;
        $this->divisi_id = $row->divisi_id;
        $this->foto_ktp = $row->foto_ktp;
        $this->foto_wajah = $row->foto_wajah;
        $this->jenis_kelamin = $row->jenis_kelamin;
        $this->tanggal_lahir = date('Y-m-d', strtotime($row->tanggal_lahir));
        $this->tanggal_masuk_kerja = date('Y-m-d', strtotime($row->tanggal_masuk_kerja));
        $this->user_id = $row->user_id;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getUserProfileId($user_profile_id)
    {
        $row = UserProfile::find($user_profile_id);
        $this->user_id = $row->user_id;
    }

    public function toggleForm($form)
    {
        $this->_reset();
        $this->form_active = $form;
        $this->emit('loadForm');
    }

    public function showModal()
    {
        $this->_reset();
        $this->emit('showModal');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->user_profile_id = null;
        $this->name = null;
        $this->email = null;
        $this->alamat = null;
        $this->cabang_id = null;
        $this->divisi_id = null;
        $this->foto_ktp_path = null;
        $this->foto_wajah_path = null;
        $this->jenis_kelamin = null;
        $this->tanggal_lahir = null;
        $this->tanggal_masuk_kerja = null;
        $this->user_id = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
