<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span><i class="fas fa-arrow-left mr-3"></i>tbl user profiles</span>
                        </a>
                        <div class="pull-right">
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            @if (auth()->user()->hasTeamPermission($curteam, $route_name.':create'))
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i> Add
                                New</button>
                            @endif
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            @if ($form_active)
            <div class="card">
                <div class="card-body">
                    <x-text-field type="text" name="name" label="Nama" />
                    <x-text-field type="email" name="email" label="Email" />

                    <x-select name="cabang_id" label="Cabang">
                        <option value="">Select Cabang</option>
                        @foreach ($cabangs as $cabang)
                        <option value="{{$cabang->id}}">{{$cabang->nama_cabang}}</option>
                        @endforeach
                    </x-select>
                    <x-select name="divisi_id" label="Divisi">
                        <option value="">Select Divisi</option>
                        @foreach ($divisis as $divisi)
                        <option value="{{$divisi->id}}">{{$divisi->nama_divisi}}</option>
                        @endforeach
                    </x-select>
                    <x-input-photo foto="{{$foto_ktp}}" path="{{optional($foto_ktp_path)->temporaryUrl()}}" name="foto_ktp_path" label="Foto Ktp" />
                    <x-input-photo foto="{{$foto_wajah}}" path="{{optional($foto_wajah_path)->temporaryUrl()}}" name="foto_wajah_path" label="Foto Wajah" />
                    <x-select name="jenis_kelamin" label="Jenis Kelamin">
                        <option value="">Select Jenis Kelamin</option>
                        <option value="Laki-Laki">Laki-Laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </x-select>
                    <x-text-field type="date" name="tanggal_lahir" label="Tanggal Lahir" />
                    <x-text-field type="date" name="tanggal_masuk_kerja" label="Tanggal Masuk Kerja" />
                    <x-text-field type="text" name="alamat" label="Alamat" />

                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                    </div>
                </div>
            </div>
            @else
            <livewire:table.user-profile-table params="{{$route_name}}" />
            @endif

        </div>

        {{-- Modal confirm --}}
        <div id="confirm-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="my-modal-title">Konfirmasi Hapus</h5>
                    </div>
                    <div class="modal-body">
                        <p>Apakah anda yakin hapus data ini.?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" wire:click='delete' class="btn btn-danger btn-sm"><i class="fa fa-check pr-2"></i>Ya, Hapus</button>
                        <button class="btn btn-primary btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')



    <script>
        document.addEventListener('livewire:load', function(e) {
            window.livewire.on('loadForm', (data) => {
                
                
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>