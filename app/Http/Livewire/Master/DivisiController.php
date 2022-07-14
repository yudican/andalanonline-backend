<?php

namespace App\Http\Livewire\Master;

use App\Models\Divisi;
use Livewire\Component;


class DivisiController extends Component
{
    
    public $divisi_id;
    public $kode_divisi;
public $nama_divisi;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataDivisiById', 'getDivisiId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.master.divisi', [
            'items' => Divisi::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['kode_divisi'  => $this->kode_divisi,
'nama_divisi'  => $this->nama_divisi];

        Divisi::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['kode_divisi'  => $this->kode_divisi,
'nama_divisi'  => $this->nama_divisi];
        $row = Divisi::find($this->divisi_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Divisi::find($this->divisi_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'kode_divisi'  => 'required',
'nama_divisi'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataDivisiById($divisi_id)
    {
        $this->_reset();
        $row = Divisi::find($divisi_id);
        $this->divisi_id = $row->id;
        $this->kode_divisi = $row->kode_divisi;
$this->nama_divisi = $row->nama_divisi;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getDivisiId($divisi_id)
    {
        $row = Divisi::find($divisi_id);
        $this->divisi_id = $row->id;
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
        $this->divisi_id = null;
        $this->kode_divisi = null;
$this->nama_divisi = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
