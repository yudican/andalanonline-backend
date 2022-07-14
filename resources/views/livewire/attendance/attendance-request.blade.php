<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span><i class="fas fa-arrow-left mr-3 text-capitalize"></i>attendance request</span>
                        </a>
                        {{-- <div class="pull-right">
                            @if (!$form && !$modal)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            @if (auth()->user()->hasTeamPermission($curteam, $route_name.':create'))
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i> Add
                                New</button>
                            @endif
                            @endif
                        </div> --}}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <livewire:table.attendance-request-table params="{{$route_name}}" />
        </div>

        {{-- Modal form --}}
        <div id="form-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">{{$update_mode ? 'Update' : 'Tambah'}} attendance request</h5>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong>Request Type</strong></span>
                                <span>{{$request_type}}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong>Request Shift</strong></span>
                                <span>{{$request_shift}}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong>Request Date</strong></span>
                                <span>{{date('d F Y',strtotime($request_date))}}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong>Request Date</strong></span>
                                <span>{{date('d F Y',strtotime($request_end_date))}}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong>Request Note</strong></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                                {{$request_description}}
                            </li>
                            @if ($request_photo)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong> Request Photo</strong></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center  border-0">
                                <img src="{{asset('storage/'.$request_photo)}}" alt="">
                            </li>
                            @endif
                        </ul>

                    </div>
                    <div class="modal-footer">

                        @if ($request_status == 0)
                        <button type="button" wire:click='approve' class="btn btn-success btn-sm"><i class="fa fa-check pr-2"></i>Setujui</button>
                        <button type="button" wire:click='decline' class="btn btn-warning btn-sm"><i class="fa fa-check pr-2"></i>Setujui</button>
                        @endif


                        <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Tutup</a>

                    </div>
                </div>
            </div>
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
            window.livewire.on('showModal', (data) => {
                $('#form-modal').modal('show')
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
                $('#form-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>