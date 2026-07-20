@extends('layouts.master')

@section('content')

<div class="container">

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>
    @endif

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4>Master Bahan</h4>

        <button class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#materialModal"
                onclick="openCreateModal()">

            Tambah Bahan

        </button>

    </div>

    {{-- TABLE --}}
    <table class="table table-bordered">

        <thead>

            <tr>

                <th width="50">No</th>

                <th>Nama Bahan</th>

                <th>Deskripsi</th>

                <th width="150">Total Stock</th>

                <th width="150">Status</th>

                <th width="220">Aksi</th>

            </tr>

        </thead>

        <tbody>

            @forelse($materials as $material)

                @php

                    $stock =
                        $material->stock->stock_kg ?? 0;

                @endphp

                <tr>

                    <td>
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        {{ $material->name }}
                    </td>

                    <td>
                        {{ $material->description ?? '-' }}
                    </td>

                    <td>
                        {{ $stock }} KG
                    </td>

                    <td>

                        @if($stock < 10)

                            <span class="badge bg-danger">

                                Stock Menipis

                            </span>

                        @elseif($stock < 30)

                            <span class="badge bg-warning text-dark">

                                Stock Sedang

                            </span>

                        @else

                            <span class="badge bg-success">

                                Stock Aman

                            </span>

                        @endif

                    </td>

                    <td class="text-nowrap">

                        {{-- EDIT --}}
                        <button class="btn btn-warning btn-sm"

                                data-bs-toggle="modal"
                                data-bs-target="#materialModal"

                                onclick="openEditModal(
                                    '{{ $material->id }}',
                                    `{{ $material->name }}`,
                                    `{{ $material->description }}`
                                )">

                            Edit

                        </button>

                        {{-- DELETE --}}
                        <form action="{{ route('raw_material_master.destroy', $material->id) }}"
                              method="POST"
                              class="d-inline delete-form">

                            @csrf
                            @method('DELETE')

                            <button type="button"
                                    class="btn btn-danger btn-sm btn-delete">

                                Hapus

                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6"
                        class="text-center">

                        Data bahan kosong

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>

{{-- CREATE / EDIT MODAL --}}
<div class="modal fade"
     id="materialModal"
     tabindex="-1">

    <div class="modal-dialog">

        <form id="materialForm"
              method="POST"
              class="modal-content">

            @csrf

            <div id="methodField"></div>

            <div class="modal-header">

                <h5 class="modal-title"
                    id="materialModalLabel">

                    Tambah Bahan

                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="mb-3">

                    <label>Nama Bahan</label>

                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control"
                           required>

                </div>

                <div class="mb-3">

                    <label>Deskripsi</label>

                    <textarea name="description"
                              id="description"
                              class="form-control"
                              rows="3"></textarea>

                </div>

            </div>

            <div class="modal-footer">

                <button type="submit"
                        class="btn btn-primary">

                    Save

                </button>

            </div>

        </form>

    </div>

</div>

@endsection

@push('scripts')

<script>

function openCreateModal()
{
    document.getElementById('materialModalLabel')
        .innerText = 'Tambah Bahan';

    document.getElementById('materialForm')
        .action =
            "{{ route('raw_material_master.store') }}";

    document.getElementById('methodField')
        .innerHTML = '';

    document.getElementById('name').value = '';

    document.getElementById('description').value = '';
}

function openEditModal(
    id,
    name,
    description
)
{
    document.getElementById('materialModalLabel')
        .innerText = 'Edit Bahan';

    document.getElementById('materialForm')
        .action =
            `/raw_material_master/${id}`;

    document.getElementById('methodField')
        .innerHTML =
            '@method("PUT")';

    document.getElementById('name').value =
        name;

    document.getElementById('description').value =
        description ?? '';
}

document.addEventListener(
    "DOMContentLoaded",
    function ()
{
    document.querySelectorAll('.btn-delete')
        .forEach(button => {

        button.addEventListener('click', function ()
        {
            let form =
                this.closest('.delete-form');

            Swal.fire({

                title: 'Yakin hapus?',
                text: 'Data bahan akan dihapus!',
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

@endpush