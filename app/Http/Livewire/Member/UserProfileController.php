<?php

namespace App\Http\Livewire\Member;

use App\Models\UserProfile;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class UserProfileController extends Component
{
    use WithFileUploads;
    public $user_profile_id;
    public $tanggal_lahir;
    public $tanggal_masuk_kerja;
    public $jenis_kelamin;
    public $alamat;
    public $foto_ktp;
    public $foto_wajah;
    public $cabang_id;
    public $divisi_id;
    public $user_id;
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
            'items' => UserProfile::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        $foto_wajah = $this->foto_wajah_path->store('upload', 'public');
        $data = [
            'tanggal_lahir'  => $this->tanggal_lahir,
            'tanggal_masuk_kerja'  => $this->tanggal_masuk_kerja,
            'jenis_kelamin'  => $this->jenis_kelamin,
            'alamat'  => $this->alamat,
            'foto_ktp'  => $this->foto_ktp,
            'foto_wajah'  => $foto_wajah,
            'cabang_id'  => $this->cabang_id,
            'divisi_id'  => $this->divisi_id,
            'user_id'  => $this->user_id
        ];

        UserProfile::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'tanggal_lahir'  => $this->tanggal_lahir,
            'tanggal_masuk_kerja'  => $this->tanggal_masuk_kerja,
            'jenis_kelamin'  => $this->jenis_kelamin,
            'alamat'  => $this->alamat,
            'foto_ktp'  => $this->foto_ktp,
            'foto_wajah'  => $this->foto_wajah,
            'cabang_id'  => $this->cabang_id,
            'divisi_id'  => $this->divisi_id,
            'user_id'  => $this->user_id
        ];
        $row = UserProfile::find($this->user_profile_id);


        if ($this->foto_wajah_path) {
            $foto_wajah = $this->foto_wajah_path->store('upload', 'public');
            $data = ['foto_wajah' => $foto_wajah];
            if (Storage::exists('public/' . $this->foto_wajah)) {
                Storage::delete('public/' . $this->foto_wajah);
            }
        }

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        UserProfile::find($this->user_profile_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'tanggal_lahir'  => 'required',
            'tanggal_masuk_kerja'  => 'required',
            'jenis_kelamin'  => 'required',
            'alamat'  => 'required',
            'foto_ktp'  => 'required',
            'cabang_id'  => 'required',
            'divisi_id'  => 'required',
            'user_id'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataUserProfileById($user_profile_id)
    {
        $this->_reset();
        $row = UserProfile::find($user_profile_id);
        $this->user_profile_id = $row->id;
        $this->tanggal_lahir = $row->tanggal_lahir;
        $this->tanggal_masuk_kerja = $row->tanggal_masuk_kerja;
        $this->jenis_kelamin = $row->jenis_kelamin;
        $this->alamat = $row->alamat;
        $this->foto_ktp = $row->foto_ktp;
        $this->foto_wajah = $row->foto_wajah;
        $this->cabang_id = $row->cabang_id;
        $this->divisi_id = $row->divisi_id;
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
        $this->user_profile_id = $row->id;
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
        $this->tanggal_lahir = null;
        $this->tanggal_masuk_kerja = null;
        $this->jenis_kelamin = null;
        $this->alamat = null;
        $this->foto_ktp = null;
        $this->foto_wajah_path = null;
        $this->cabang_id = null;
        $this->divisi_id = null;
        $this->user_id = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
