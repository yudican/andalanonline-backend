<?php

namespace App\Http\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\ScheduleShift;
use App\Models\User;
use DateInterval;
use DatePeriod;
use DateTime;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class AttendanceRequestController extends Component
{
    use WithFileUploads;
    public $attendance_request_id;
    public $user_id;
    public $request_type;
    public $request_photo;
    public $request_description;
    public $request_date;
    public $request_end_date;
    public $request_status;
    public $shift_id;
    public $request_photo_path;
    public $request_shift;


    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataAttendanceRequestById', 'getAttendanceRequestId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.attendance.attendance-request', [
            'items' => AttendanceRequest::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        $request_photo = $this->request_photo_path->store('upload', 'public');
        $data = [
            'user_id'  => $this->user_id,
            'request_type'  => $this->request_type,
            'request_photo'  => $request_photo,
            'request_description'  => $this->request_description,
            'request_date'  => $this->request_date,
            'request_end_date'  => $this->request_end_date,
            'request_status'  => $this->request_status,
            'shift_id'  => $this->shift_id
        ];

        AttendanceRequest::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'user_id'  => $this->user_id,
            'request_type'  => $this->request_type,
            'request_photo'  => $this->request_photo,
            'request_description'  => $this->request_description,
            'request_date'  => $this->request_date,
            'request_end_date'  => $this->request_end_date,
            'request_status'  => $this->request_status,
            'shift_id'  => $this->shift_id
        ];
        $row = AttendanceRequest::find($this->attendance_request_id);


        if ($this->request_photo_path) {
            $request_photo = $this->request_photo_path->store('upload', 'public');
            $data = ['request_photo' => $request_photo];
            if (Storage::exists('public/' . $this->request_photo)) {
                Storage::delete('public/' . $this->request_photo);
            }
        }

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        AttendanceRequest::find($this->attendance_request_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'user_id'  => 'required',
            'request_type'  => 'required',
            'request_description'  => 'required',
            'request_date'  => 'required',
            'request_end_date'  => 'required',
            'request_status'  => 'required',
            'shift_id'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataAttendanceRequestById($attendance_request_id)
    {
        $this->_reset();
        $row = AttendanceRequest::find($attendance_request_id);
        $this->attendance_request_id = $row->id;
        $this->user_id = $row->user_id;
        $this->request_type = $row->request_type;
        $this->request_photo = $row->request_photo;
        $this->request_description = $row->request_description;
        $this->request_date = $row->request_date;
        $this->request_end_date = $row->request_end_date;
        $this->request_status = $row->request_status;
        $this->shift_id = $row->shift_id;
        $this->request_shift = $row->shift->nama_shift;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getAttendanceRequestId($attendance_request_id)
    {
        $row = AttendanceRequest::find($attendance_request_id);
        $this->attendance_request_id = $row->id;
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
        $this->attendance_request_id = null;
        $this->user_id = null;
        $this->request_type = null;
        $this->request_photo_path = null;
        $this->request_description = null;
        $this->request_date = null;
        $this->request_end_date = null;
        $this->request_status = null;
        $this->shift_id = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }

    public function approve()
    {
        $periods = new DatePeriod(
            new DateTime($this->request_date),
            new DateInterval('P1D'),
            new DateTime($this->request_end_date)
        );

        $row = AttendanceRequest::find($this->attendance_request_id);
        $row->update(['request_status' => 1]);
        if ($this->request_type != 'lembur') {
            foreach ($periods as $key => $value) {
                // $value->format('Y-m-d')
                $shifts = ScheduleShift::where('shift_id', $this->shift_id)->get();

                $users = User::whereHas('roles', function ($query) {
                    return $query->where('role_type', 'member');
                })->where('status', 1)->get();

                $data = [];
                foreach ($users as $key => $user) {
                    foreach ($shifts as $key => $shift) {
                        $attendance = Attendance::whereDate('created_at', date('Y-m-d'))->where('user_id', $user->id)->where('schedule_shift_id', $shift->id)->first();
                        if (!$attendance) {
                            if ($shift->schedule_type == 'checkin') {
                                $data[] = [
                                    'user_id' => $user->id,
                                    'schedule_shift_id' => $shift->id,
                                    'attendance_date' => $value->format('Y-m-d'),
                                    'attendance_status' => statusAbsen($this->request_type),
                                    'attendance_request_status' => 1,
                                    'attendance_photo' => null,
                                    'attendance_note' => $this->request_type,
                                    'attendance_active' => false,
                                    'attendance_day'  => date('Y-m-d'),
                                    'attendance_shift'  => $shift->shift_id,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            } else {
                                $data[] = [
                                    'user_id' => $user->id,
                                    'schedule_shift_id' => $shift->id,
                                    'attendance_date' => $value->format('Y-m-d'),
                                    'attendance_status' => statusAbsen($this->request_type),
                                    'attendance_request_status' => 1,
                                    'attendance_photo' => null,
                                    'attendance_note' => $this->request_type,
                                    'attendance_active' => false,
                                    'attendance_day'  => date('Y-m-d'),
                                    'attendance_shift'  => $shift->shift_id,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            }
                        }
                    }
                }

                Attendance::insert($data);
            }
        }
        $this->emit('closeModal');
        $this->emit('refreshTable');
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function decline()
    {
        $row = AttendanceRequest::find($this->attendance_request_id);
        $row->update(['request_status' => 2]);

        $this->emit('closeModal');
        $this->emit('refreshTable');
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }
}
