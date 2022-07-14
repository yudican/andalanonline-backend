<?php

namespace App\Http\Livewire;

use App\Models\ScheduleShift;
use App\Models\Shift;
use Livewire\Component;


class ScheduleShiftController extends Component
{

    public $schedule_shift_id;
    public $schedule_time;
    public $schedule_title;
    public $schedule_type;
    public $shift_id;

    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataScheduleShiftById', 'getScheduleShiftId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.schedule-shifts', [
            'types' => ['checkin', 'checkout', 'breakin', 'breakout'],
            'shifts' => Shift::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'schedule_time'  => $this->schedule_time,
            'schedule_title'  => $this->schedule_title,
            'schedule_type'  => $this->schedule_type,
            'shift_id'  => $this->shift_id
        ];

        ScheduleShift::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'schedule_time'  => $this->schedule_time,
            'schedule_title'  => $this->schedule_title,
            'schedule_type'  => $this->schedule_type,
            'shift_id'  => $this->shift_id
        ];
        $row = ScheduleShift::find($this->schedule_shift_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        ScheduleShift::find($this->schedule_shift_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'schedule_time'  => 'required',
            'schedule_title'  => 'required',
            'schedule_type'  => 'required',
            'shift_id'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataScheduleShiftById($schedule_shift_id)
    {
        $this->_reset();
        $row = ScheduleShift::find($schedule_shift_id);
        $this->schedule_shift_id = $row->id;
        $this->schedule_time = $row->schedule_time;
        $this->schedule_title = $row->schedule_title;
        $this->schedule_type = $row->schedule_type;
        $this->shift_id = $row->shift_id;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getScheduleShiftId($schedule_shift_id)
    {
        $row = ScheduleShift::find($schedule_shift_id);
        $this->schedule_shift_id = $row->id;
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
        $this->schedule_shift_id = null;
        $this->schedule_time = null;
        $this->schedule_title = null;
        $this->schedule_type = null;
        $this->shift_id = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
