<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\AttendanceRequest;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class AttendanceRequestTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_attendance_request';
    public $hide = [];

    public function builder()
    {
        return AttendanceRequest::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('user.name')->label('User')->searchable(),
            Column::name('request_type')->label('Request Type')->searchable(),
            Column::callback(['request_photo'], function ($image) {
                return view('livewire.components.photo', [
                    'image_url' => asset('storage/' . $image),
                ]);
            })->label(__('Request Photo')),
            Column::name('request_description')->label('Request Description')->searchable(),
            Column::name('request_date')->label('Request Date')->searchable(),
            Column::name('request_end_date')->label('Request End Date')->searchable(),
            Column::name('shift.nama_shift')->label('Shift')->searchable(),

            Column::callback(['id'], function ($id) {
                return '<button class="btn btn-primary btn-sm" wire:click="getDataById(' . $id . ')">Detail</button>';
            })->label(__('Aksi')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataAttendanceRequestById', $id);
    }

    public function getId($id)
    {
        $this->emit('getAttendanceRequestId', $id);
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
