<?php

namespace App\Http\Livewire\Master;

use App\Models\Cabang;
use Livewire\Component;


class CabangController extends Component
{
    
    public $cabang_id;
    public $kode_cabang;
public $lokasi_cabang;
public $nama_cabang;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataCabangById', 'getCabangId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.master.cabang', [
            'items' => Cabang::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['kode_cabang'  => $this->kode_cabang,
'lokasi_cabang'  => $this->lokasi_cabang,
'nama_cabang'  => $this->nama_cabang];

        Cabang::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['kode_cabang'  => $this->kode_cabang,
'lokasi_cabang'  => $this->lokasi_cabang,
'nama_cabang'  => $this->nama_cabang];
        $row = Cabang::find($this->cabang_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Cabang::find($this->cabang_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'kode_cabang'  => 'required',
'lokasi_cabang'  => 'required',
'nama_cabang'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataCabangById($cabang_id)
    {
        $this->_reset();
        $row = Cabang::find($cabang_id);
        $this->cabang_id = $row->id;
        $this->kode_cabang = $row->kode_cabang;
$this->lokasi_cabang = $row->lokasi_cabang;
$this->nama_cabang = $row->nama_cabang;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getCabangId($cabang_id)
    {
        $row = Cabang::find($cabang_id);
        $this->cabang_id = $row->id;
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
        $this->cabang_id = null;
        $this->kode_cabang = null;
$this->lokasi_cabang = null;
$this->nama_cabang = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
