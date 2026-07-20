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

        var akunModal = new bootstrap.Modal(
            document.getElementById('obrasModal')
        );

        akunModal.show();

        document.getElementById('username').value = '';

        document.getElementById('password').value = '';
    });
</script>
@endif
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#obrasModal" onclick="openCreateModal()">Tambah Kurir</button>

<div class="row text-center g-3">
    @foreach ($couriers as $courier)
        <div class="col-md-4">
            <div class="position-relative p-4 bg-white shadow-sm rounded">

                <!-- Tombol hapus -->
                <form action="{{ route('courier.destroy', $courier->id) }}" method="POST" class="position-absolute top-0 end-0 m-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(this.form)">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>

                <!-- Klik card untuk edit -->
                <div onclick="openEditModal({{ $courier->id }}, '{{ $courier->name }}', '{{ $courier->username }}', '{{ $courier->phone }}')" style="cursor:pointer;">
                    <h6>Kurir {{ $loop->iteration }}</h6>
                    <h2 class="text-primary">{{ $courier->name }}</h2>
                    <div class="badge bg-primary text-wrap" style="width: 6rem;">
                        Available
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>


<!-- Modal -->
<div class="modal fade" id="obrasModal" tabindex="-1" aria-labelledby="obrasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('courier.store') }}" method="POST" class="modal-content" autocomplete="off">
            @csrf
            <input type="hidden" name="id" id="courier_id">

            <div class="modal-header">
                <h5 class="modal-title" id="obrasModalLabel">Tambah Kurir</h5>
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

                <div class="mb-3" id="usernameField">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" id="username" required autocomplete="new-password">
                </div>

                <div class="mb-3" id="passwordField">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" required autocomplete="new-password">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor Telepon</label>
                    <input type="text" class="form-control" name="phone" id="phone" required value="{{ old('phone') }}">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('obrasModalLabel').innerText = 'Tambah Kurir';
    document.getElementById('courier_id').value = '';
    document.getElementById('name').value = '';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('phone').value = '';

    document.getElementById('passwordField').style.display = 'block';
    document.getElementById('usernameField').style.display = 'block';
    document.getElementById('password').required = true;
    document.getElementById('username').readOnly = false;
}

function openEditModal(id, name, username, phone) {
    document.getElementById('obrasModalLabel').innerText = 'Edit Kurir';
    document.getElementById('courier_id').value = id;
    document.getElementById('name').value = name;
    document.getElementById('username').value = username;
    document.getElementById('password').value = '';
    document.getElementById('phone').value = phone;

    document.getElementById('passwordField').style.display = 'none';
    document.getElementById('usernameField').style.display = 'none';
    document.getElementById('password').required = false;
    document.getElementById('username').readOnly = true;
    var modal = new bootstrap.Modal(document.getElementById('obrasModal'));
    modal.show();
}

function confirmDelete(form) {
    if (confirm("Yakin ingin menghapus Kurir ini?")) {
        form.submit();
    }
}

</script>
@endsection
