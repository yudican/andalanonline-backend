<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\UserProfile;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\User;

class UserProfileTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_user_profiles';
    public $hide = [];

    public function builder()
    {
        return UserProfile::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('user.name')->label('Nama')->searchable(),
            Column::name('cabang.nama_cabang')->label('Cabang')->searchable(),
            Column::name('divisi.nama_divisi')->label('Divisi')->searchable(),
            // Column::callback(['foto_ktp'], function ($image) {
            //     return view('livewire.components.photo', [
            //         'image_url' => asset('storage/' . $image),
            //     ]);
            // })->label(__('Foto Ktp')),
            // Column::callback(['foto_wajah'], function ($image) {
            //     return view('livewire.components.photo', [
            //         'image_url' => asset('storage/' . $image),
            //     ]);
            // })->label(__('Foto Wajah')),
            Column::name('jenis_kelamin')->label('Jenis Kelamin')->searchable(),
            Column::name('tanggal_lahir')->label('Tanggal Lahir')->searchable(),
            Column::name('tanggal_masuk_kerja')->label('Tanggal Masuk Kerja')->searchable(),
            Column::callback(['tbl_users.status', $this->table_name . '.user_id'], function ($status, $id) {
                return view('livewire.components.toggle-status', [
                    'id' => $id,
                    'active' => $status,
                    'field' => 'status',
                ]);
            })->label(__('Status')),
            Column::callback(['id'], function ($id) {
                return view('livewire.components.action-button', [
                    'id' => $id,
                    'segment' => $this->params
                ]);
            })->label(__('Aksi')),
        ];
    }
    public function toggleStatus($id, $field)
    {
        $user = User::find($id);
        if ($user) {
            $user->update(['status' => $user->status == 0 ? 1 : 0]);
        }
        $this->emit('showAlert', ['msg' => 'Status Berhasil Diupdate']);
    }
    public function getDataById($id)
    {
        $this->emit('getDataUserProfileById', $id);
    }

    public function getId($id)
    {
        $this->emit('getUserProfileId', $id);
    }

    public function refreshTable()
    {
        $this->emit('refreshLivewireDatatable');
    }

    public function toggle($index)
    {
        if ($this->sort == $index) {
            $this->initialiseSort();
        }

        $column = HideableColumn::where([
            'table_name' => $this->table_name,
            'column_name' => $this->columns[$index]['name'],
            'index' => $index,
            'user_id' => auth()->user()->id
        ])->first();

        if (!$this->columns[$index]['hidden']) {
            unset($this->activeSelectFilters[$index]);
        }

        $this->columns[$index]['hidden'] = !$this->columns[$index]['hidden'];

        if (!$column) {
            HideableColumn::updateOrCreate([
                'table_name' => $this->table_name,
                'column_name' => $this->columns[$index]['name'],
                'index' => $index,
                'user_id' => auth()->user()->id
            ]);
        } else {
            $column->delete();
        }
    }
}
