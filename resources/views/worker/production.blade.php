@extends('layouts.master')
@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">

        {{ session('error') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>   

    </div>
@endif
@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {

        var modal = new bootstrap.Modal(
            document.getElementById('akunModal')
        );

        modal.show();
    });
</script>
@endif
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#obrasModal" onclick="openCreateModal()">Tambah Pekerja Produksi</button>
<form method="GET" id="filterForm">
    <select 
        name="role" 
        class="form-select mb-4"
        onchange="document.getElementById('filterForm').submit()"
    >
        <option value="">Semua Pekerja</option>

        <option value="cutter" 
            {{ $selectedRole == 'cutter' ? 'selected' : '' }}>
            Pemotong
        </option>

        <option value="overdeck"
            {{ $selectedRole == 'overdeck' ? 'selected' : '' }}>
            Overdeck
        </option>

        <option value="tailor"
            {{ $selectedRole == 'tailor' ? 'selected' : '' }}>
            Penjahit
        </option>

        <option value="obras"
            {{ $selectedRole == 'obras' ? 'selected' : '' }}>
            Obras
        </option>
    </select>
</form>
<div class="row text-center g-3">
    @foreach ($productions as $production)
        <div class="col-md-4">
            <div class="position-relative p-4 bg-white shadow-sm rounded">

                <div class="position-absolute top-0 end-0 m-2 d-flex flex-column align-items-end gap-2">

                    <!-- Tombol hapus -->
                    <form action="{{ route('workers.destroy', $production->id) }}"
                        method="POST"
                        class="delete-form">

                        @csrf
                        @method('DELETE')

                        <button type="button"
                                class="btn btn-sm btn-danger btn-delete">
                            <i class="bi bi-trash"></i>
                        </button>

                    </form>
                </div>

                <!-- Klik card untuk edit -->
                <div onclick="openEditModal({{ $production->id }}, '{{ $production->name }}', '{{ $production->phone }}', '{{ $production->role  }}', '{{ $production->overdeck_type }}', '{{ $production->address }}', '{{ $production->rate_per_piece }}', '{{ $production->latitude }}',
    '{{ $production->longitude }}')" style="cursor:pointer;">
                    <h6>
                        @php
                            $roles = [
                                'cutter' => 'Pemotong',
                                'tailor' => 'Penjahit',
                                'overdeck' => 'Overdeck',
                                'obras' => 'Obras'
                            ];
                        @endphp
                         {{ $roles[$production->role] ?? 'Pekerja' }}
                    </h6>
                    <h2 class="text-primary">{{ $production->name }}</h2>
                    <div class="badge bg-primary text-wrap" style="width: 6rem;">
                        Available
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>


<!-- Modal TAMBAH -->
<div class="modal fade" id="obrasModal" tabindex="-1" aria-labelledby="obrasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('workers.store') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="id" id="production_id">

            <div class="modal-header">
                <h5 class="modal-title" id="obrasModalLabel">Tambah Pekerja Produksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-danger text-center">
                        {{ $errors->first() }}
                    </div>
                @endif
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" class="form-control" name="name" id="name" required value="{{ old('name') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor Telepon</label>
                    <input type="text" class="form-control" name="phone" id="phone" required value="{{ old('phone') }}">
                </div>

                <div class="mb-3" id="accountFields">
                    <label class="form-label">Username</label>
                    <input
                        type="text"
                        class="form-control"
                        name="username"
                        id="username"
                        autocomplete="off">
                </div>

                <div class="mb-3" id="passwordField">
                    <label class="form-label">Password</label>
                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        id="password"
                        autocomplete="new-password">
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipe Pekerja</label>
                    <select class="form-select" name="role" id="role">
                        <option selected>Tipe Pekerja</option>
                        <option value="cutter">Pemotong</option>
                        <option value="overdeck">Overdek</option>
                        <option value="tailor">Penjahit</option>
                        <option value="obras">Obras</option>
                    </select>
                </div>

                <div class="mb-3" id="overdeckField" style="display:none;">
                    <label class="form-label">Tipe Overdeck</label>

                    <select class="form-select" name="overdeck_type" id="overdeck_type">
                        <option value="">Pilih Tipe Overdeck</option>
                        <option value="tangan">Tangan</option>
                        <option value="bawah">Bawah</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Per Baju</label>
                    <input type="text" class="form-control" name="rate_per_piece" id="rate_per_piece" required value="{{ old('rate_per_piece') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat</label>

                    <div class="input-group">
                        <input
                            type="text"
                            name="address"
                            class="form-control"
                            id="address"
                            placeholder="Pilih lokasi dari map"
                            readonly
                            value="{{ old('address') }}"
                        >

                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="openMapModal()"
                        >
                            <i class="bi bi-geo-alt"></i>
                        </button>
                    </div>
                </div>

                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL MAPS --}}
<div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Pilih Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <div class="input-group">

                        <input
                            type="text"
                            id="searchLocation"
                            class="form-control"
                            placeholder="Cari lokasi..."
                        >

                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="searchLocation()"
                        >
                            Cari
                        </button>

                    </div>
                </div>

                <div id="map" style="height:500px;border-radius:10px;"></div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<script>

function openCreateModal() {

    document.getElementById('obrasModalLabel').innerText =
        'Tambah Pekerja Produksi';

    document.getElementById('production_id').value = '';

    document.getElementById('name').value = '';
    document.getElementById('phone').value = '';

    // Tampilkan field akun
    document.getElementById('accountFields').style.display = 'block';
    document.getElementById('passwordField').style.display = 'block';

    // Username dan password wajib diisi saat CREATE
    document.getElementById('username').setAttribute('required', 'required');
    document.getElementById('password').setAttribute('required', 'required');

    // Kosongkan akun
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';

    document.getElementById('role').value = '';
    document.getElementById('overdeck_type').value = '';

    document.getElementById('address').value = '';
    document.getElementById('latitude').value = '';
    document.getElementById('longitude').value = '';

    document.getElementById('rate_per_piece').value = '';

    document.getElementById('overdeckField').style.display = 'none';
}

document.getElementById('role').addEventListener('change', function () {

    if (this.value === 'overdeck') {
        document.getElementById('overdeckField').style.display = 'block';
    } else {
        document.getElementById('overdeckField').style.display = 'none';
        document.getElementById('overdeck_type').value = '';
    }

});

function openEditModal(
    id,
    name,
    phone,
    role,
    overdeck_type,
    address,
    rate_per_piece,
    latitude,
    longitude
) {

    document.getElementById('obrasModalLabel').innerText =
        'Edit Pekerja Produksi';

    document.getElementById('production_id').value = id;

    document.getElementById('name').value = name;
    document.getElementById('phone').value = phone;

    document.getElementById('role').value = role;
    document.getElementById('overdeck_type').value = overdeck_type;

    document.getElementById('rate_per_piece').value = rate_per_piece;

    document.getElementById('address').value = address;
    document.getElementById('latitude').value = latitude;
    document.getElementById('longitude').value = longitude;

    // ==========================================
    // AKUN TIDAK DITAMPILKAN SAAT EDIT
    // ==========================================

    document.getElementById('accountFields').style.display = 'none';
    document.getElementById('passwordField').style.display = 'none';

    // Username dan password tidak wajib
    document.getElementById('username').removeAttribute('required');
    document.getElementById('password').removeAttribute('required');

    // Kosongkan value agar tidak ikut terkirim
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';

    // ==========================================
    // OVERDECK
    // ==========================================

    if (role === 'overdeck') {
        document.getElementById('overdeckField').style.display = 'block';
    } else {
        document.getElementById('overdeckField').style.display = 'none';
    }

    var modal = new bootstrap.Modal(
        document.getElementById('obrasModal')
    );

    modal.show();
}

// DELETE CONFIRMATION
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.btn-delete')
        .forEach(button => {

            button.addEventListener('click', function () {

                let form = this.closest('.delete-form');

                Swal.fire({

                    title: 'Yakin hapus?',
                    text: 'Data pekerja produksi akan dihapus!',
                    icon: 'warning',

                    showCancelButton: true,

                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',

                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'

                }).then((result) => {

                    if (result.isConfirmed) {
                        form.submit();
                    }

                });

            });

        });

});
</script>
@endsection
@push('scripts')

<script src="{{ asset('js/worker-map.js') }}"></script>

@endpush
