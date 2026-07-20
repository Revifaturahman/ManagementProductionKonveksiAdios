@extends('layouts.master')

@section('content')

<div class="container">

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>
    @endif

    {{-- ERROR DARI LOGIC --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>
    @endif

    {{-- ERROR VALIDASI --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">

            {{ $errors->first() }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>
    @endif

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4>Penerimaan Bahan</h4>

        <button class="btn btn-primary"
                onclick="openCreateModal()">

            Tambah

        </button>

    </div>

    {{-- TABLE --}}

            <table class="table table-bordered">

                <thead>

                    <tr>

                        <th width="50">No</th>

                        <th>Tanggal</th>

                        <th>Nama Bahan</th>

                        <th width="150">Berat (KG)</th>

                        <th>Catatan</th>

                        <th width="180">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($raw_material_stock_movements as $movement)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse(
                                    $movement->transaction_date
                                )->format('d M Y') }}
                            </td>

                            <td>
                                {{ $movement->rawMaterialMaster->name ?? '-' }}
                            </td>
                            <td>

                                <span class="text-success">

                                    +{{ $movement->qty_kg }} KG

                                </span>

                            </td>

                            <td>
                                {{ $movement->notes ?? '-' }}
                            </td>

                            <td class="text-nowrap">

                                {{-- EDIT --}}
                                <button class="btn btn-warning btn-sm"

                                        onclick="openEditModal(
                                            '{{ $movement->id }}',
                                            '{{ $movement->raw_material_master_id }}',
                                            '{{ $movement->qty_kg }}',
                                            '{{ $movement->transaction_date }}',
                                            `{{ $movement->notes }}`
                                        )">

                                    Edit

                                </button>

                                {{-- DELETE --}}
                                <form action="{{ route(
                                        'raw_material_stock_movement.destroy',
                                        $movement->id
                                    ) }}"
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

                            <td colspan="7"
                                class="text-center">

                                Data movement stock kosong

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

</div>

{{-- CREATE / EDIT MODAL --}}
<div class="modal fade"
     id="movementModal"
     tabindex="-1">

    <div class="modal-dialog">

        <form id="movementForm"
              method="POST"
              class="modal-content">

            @csrf

            <div id="methodField"></div>

            <div class="modal-header">

                <h5 class="modal-title"
                    id="movementModalLabel">

                    Penerimaan Bahan

                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                {{-- BAHAN --}}
                <div class="mb-3">

                    <label>Bahan</label>

                    <select name="raw_material_master_id"
                            id="raw_material_master_id"
                            class="form-control"
                            required>

                        @foreach($materials as $material)

                            <option value="{{ $material->id }}">

                                {{ $material->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                    <input type="hidden"
                    name="type"
                    value="in">


                {{-- QTY --}}
                <div class="mb-3">

                    <label>Berat (KG)</label>

                    <input type="number"
                           step="0.01"
                           name="qty_kg"
                           id="qty_kg"
                           class="form-control"
                           required>

                </div>

                {{-- TANGGAL --}}
                <div class="mb-3">

                    <label>Tanggal</label>

                    <input type="date"
                           name="transaction_date"
                           id="transaction_date"
                           class="form-control"
                           required>

                </div>

                {{-- NOTES --}}
                <div class="mb-3">

                    <label>Catatan</label>

                    <textarea name="notes"
                              id="notes"
                              class="form-control"></textarea>

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
    document.getElementById('movementModalLabel')
        .innerText = 'Tambah Penerimaan Bahan';

    document.getElementById('movementForm')
        .action =
            "{{ route('raw_material_stock_movement.store') }}";

    document.getElementById('methodField')
        .innerHTML = '';

    document.getElementById(
        'raw_material_master_id'
    ).value = '';

    document.getElementById('qty_kg').value = '';

    document.getElementById(
        'transaction_date'
    ).value = '';

    document.getElementById('notes').value = '';

    let modal = new bootstrap.Modal(
        document.getElementById('movementModal')
    );

    modal.show();
}

function openEditModal(
    id,
    materialId,
    qty,
    transactionDate,
    notes
)
{
    document.getElementById('movementModalLabel')
        .innerText = 'Edit Penerimaan Bahan';

    document.getElementById('movementForm')
        .action =
            `/raw_material_stock_movement/${id}`;

    document.getElementById('methodField')
        .innerHTML =
            '@method("PUT")';

    document.getElementById(
        'raw_material_master_id'
    ).value = materialId;

    document.getElementById('qty_kg').value = qty;

    document.getElementById(
        'transaction_date'
    ).value = transactionDate;

    document.getElementById('notes').value = notes;

    let modal = new bootstrap.Modal(
        document.getElementById('movementModal')
    );

    modal.show();
}
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.btn-delete')
        .forEach(button => {

        button.addEventListener('click', function () {

            let form =
                this.closest('.delete-form');

            Swal.fire({

                title: 'Yakin hapus?',
                text: 'Data bahan baku akan dihapus!',
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