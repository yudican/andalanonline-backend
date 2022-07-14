<?php

namespace App\Http\Livewire\Attendance;

use App\Models\Attendance;
use Livewire\Component;


class AttendanceController extends Component
{

    public $attendance_id;
    public $user_id;
    public $schedule_shift_id;
    public $attendance_date;
    public $attendance_status;
    public $attendance_request_status;
    public $attendance_photo;
    public $attendance_day;
    public $attendance_shift;
    public $attendance_note;
    public $attendance_active;



    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataAttendanceById', 'getAttendanceId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.attendance.attendances', [
            'items' => Attendance::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'user_id'  => $this->user_id,
            'schedule_shift_id'  => $this->schedule_shift_id,
            'attendance_date'  => $this->attendance_date,
            'attendance_status'  => $this->attendance_status,
            'attendance_request_status'  => $this->attendance_request_status,
            'attendance_photo'  => $this->attendance_photo,
            'attendance_day'  => $this->attendance_day,
            'attendance_shift'  => $this->attendance_shift,
            'attendance_note'  => $this->attendance_note,
            'attendance_active'  => $this->attendance_active
        ];

        Attendance::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'user_id'  => $this->user_id,
            'schedule_shift_id'  => $this->schedule_shift_id,
            'attendance_date'  => $this->attendance_date,
            'attendance_status'  => $this->attendance_status,
            'attendance_request_status'  => $this->attendance_request_status,
            'attendance_photo'  => $this->attendance_photo,
            'attendance_day'  => $this->attendance_day,
            'attendance_shift'  => $this->attendance_shift,
            'attendance_note'  => $this->attendance_note,
            'attendance_active'  => $this->attendance_active
        ];
        $row = Attendance::find($this->attendance_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Attendance::find($this->attendance_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'user_id'  => 'required',
            'schedule_shift_id'  => 'required',
            'attendance_date'  => 'required',
            'attendance_status'  => 'required',
            'attendance_request_status'  => 'required',
            'attendance_photo'  => 'required',
            'attendance_day'  => 'required',
            'attendance_shift'  => 'required',
            'attendance_note'  => 'required',
            'attendance_active'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataAttendanceById($attendance_id)
    {
        $this->_reset();
        $row = Attendance::find($attendance_id);
        $this->attendance_id = $row->id;
        $this->user_id = $row->user_id;
        $this->schedule_shift_id = $row->schedule_shift_id;
        $this->attendance_date = $row->attendance_date;
        $this->attendance_status = $row->attendance_status;
        $this->attendance_request_status = $row->attendance_request_status;
        $this->attendance_photo = $row->attendance_photo;
        $this->attendance_day = $row->attendance_day;
        $this->attendance_shift = $row->attendance_shift;
        $this->attendance_note = $row->attendance_note;
        $this->attendance_active = $row->attendance_active;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getAttendanceId($attendance_id)
    {
        $row = Attendance::find($attendance_id);
        $this->attendance_id = $row->id;
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
        $this->attendance_id = null;
        $this->user_id = null;
        $this->schedule_shift_id = null;
        $this->attendance_date = null;
        $this->attendance_status = null;
        $this->attendance_request_status = null;
        $this->attendance_photo = null;
        $this->attendance_day = null;
        $this->attendance_shift = null;
        $this->attendance_note = null;
        $this->attendance_active = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
