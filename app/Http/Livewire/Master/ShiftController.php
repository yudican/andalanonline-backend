<?php

namespace App\Http\Livewire\Master;

use App\Models\Shift;
use Livewire\Component;


class ShiftController extends Component
{
    
    public $shift_id;
    public $kode_shift;
public $nama_shift;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataShiftById', 'getShiftId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.master.shift', [
            'items' => Shift::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['kode_shift'  => $this->kode_shift,
'nama_shift'  => $this->nama_shift];

        Shift::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['kode_shift'  => $this->kode_shift,
'nama_shift'  => $this->nama_shift];
        $row = Shift::find($this->shift_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Shift::find($this->shift_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'kode_shift'  => 'required',
'nama_shift'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataShiftById($shift_id)
    {
        $this->_reset();
        $row = Shift::find($shift_id);
        $this->shift_id = $row->id;
        $this->kode_shift = $row->kode_shift;
$this->nama_shift = $row->nama_shift;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getShiftId($shift_id)
    {
        $row = Shift::find($shift_id);
        $this->shift_id = $row->id;
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
        $this->shift_id = null;
        $this->kode_shift = null;
$this->nama_shift = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
