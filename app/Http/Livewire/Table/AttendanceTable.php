<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Attendance;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class AttendanceTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_attendances';
    public $hide = [];

    public function builder()
    {
        return Attendance::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('user.name')->label('User Id')->searchable(),
            Column::name('schedule.schedule_title')->label('Schedule Shift Id')->searchable(),
            Column::name('attendance_date')->label('Attendance Date')->searchable(),
            Column::name('attendance_status')->label('Attendance Status')->searchable(),
            // Column::name('attendance_request_status')->label('Attendance Request Status')->searchable(),
            Column::callback(['attendance_photo'], function ($attendance_photo) {
                return view('livewire.components.photo', [
                    'image_url' => asset('storage/' . $attendance_photo),
                ]);
            })->label(__('Attendance Photo')),
            Column::name('attendance_day')->label('Attendance Day')->searchable(),
            Column::name('schedule.shift.nama_shift')->label('Attendance Shift')->searchable(),
            Column::name('attendance_note')->label('Attendance Note')->searchable(),

            // Column::callback(['id'], function ($id) {
            //     return view('livewire.components.action-button', [
            //         'id' => $id,
            //         'segment' => $this->params
            //     ]);
            // })->label(__('Aksi')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataAttendanceById', $id);
    }

    public function getId($id)
    {
        $this->emit('getAttendanceId', $id);
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
