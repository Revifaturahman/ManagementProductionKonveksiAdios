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

<div class="container">

    <h4 class="mb-3">Produksi Tahap 1</h4>

    {{-- <form method="GET">

        <div class="row mb-3">

            <div class="col-md-3">

                <label>
                    Dari Tanggal
                </label>

                <input
                    type="date"
                    name="start_date"
                    class="form-control"
                    value="{{ request('start_date') }}"
                >

            </div>

            <div class="col-md-3">

                <label>
                    Sampai Tanggal
                </label>

                <input
                    type="date"
                    name="end_date"
                    class="form-control"
                    value="{{ request('end_date') }}"
                >

            </div>

            <div class="col-md-2 d-flex align-items-end">

                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    Filter

                </button>

            </div>

        </div>

    </form> --}}

    <!-- BUTTON CREATE -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createModal">
        Tambah Produksi Tahap 1
    </button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kurir</th>
                <th>Product</th>
                <th>Size</th>
                <th>Berat (KG)</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
        @foreach($rawMaterials as $item)
            <tr>
                <td>{{ $item->courier->name }}</td>

                <td>
                    @foreach($item->details as $detail)
                        {{ $detail->variant->product->name ?? '-'}} <br>
                    @endforeach
                </td>

                <td>
                    @foreach($item->details as $detail)
                        {{ $detail->variant->size }} <br>
                    @endforeach
                </td>

                <td>
                    @foreach($item->details as $detail)
                        {{ rtrim(rtrim($detail->weight, '0'), '.') }} KG <br>
                    @endforeach
                </td>

                <td>{{ \Carbon\Carbon::parse($item->date)->translatedFormat('d F Y') }}</td>

                <td>
                    <span class="badge bg-info">{{ $item->status }}</span>
                </td>

                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                        Edit
                    </button>

                    {{-- <button
                        class="btn btn-info btn-sm btn-lihat"
                        data-id="{{ $item->id }}"
                        data-bs-toggle="modal"
                        data-bs-target="#lihatModal"
                    >
                        Lihat
                    </button> --}}

                    @include('components.crud.delete-button', [
                        'action' => route('rawMaterial.destroy', $item->id)
                    ])
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>

<div
    class="modal fade"
    id="lihatModal"
    tabindex="-1"
>
    <div class="modal-dialog modal-xl">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Tracking Produksi
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>
            </div>

            <div class="modal-body" id="modalDetailBody">

                <div class="text-center p-5">
                    Loading...
                </div>

            </div>

        </div>

    </div>
</div>

{{-- ================= EDIT MODALS ================= --}}
@foreach($rawMaterials as $item)
@component('components.crud.edit-modal', [
    'modalId' => 'editModal'.$item->id,
    'action'  => route('rawMaterial.update', $item->id),
    'title'   => 'Edit Produksi Tahap 1'
])

<div class="mb-2">
    <label>Kurir</label>

    <input
        type="text"
        class="form-control"
        value="{{ $couriers->first()?->name ?? 'Tidak ada kurir tersedia' }}"
        readonly
    >

    <input
        type="hidden"
        name="courier_id"
        value="{{ $couriers->first()?->id }}"
    >
</div>

<div class="mb-2">
    <label for="date">Tanggal Produksi</label>

    <input
        type="date"
        class="form-control"
        id="date"
        name="date"
        value="{{ old('date', date('Y-m-d')) }}"
    >
</div>

<hr>
<h6>Divisi Worker</h6>

@php
    $cutterProcess = $item->details
        ->flatMap->processes
        ->firstWhere('stage', 'cutter');

    $overdeckProcess = $item->details
        ->flatMap->processes
        ->firstWhere('stage', 'overdeck_tangan');
@endphp

<div class="mb-2">
    <label>Pemotong</label>

    <select
        name="cutter_worker_id"
        class="form-select"
        required
    >
        @foreach($cutters as $worker)
            <option
                value="{{ $worker->id }}"
                {{ optional($cutterProcess)->worker_id == $worker->id ? 'selected' : '' }}
            >
                {{ $worker->name }}
                - Sisa Kerjaan {{ $worker->remaining_qty }} PCS
            </option>
        @endforeach
    </select>
</div>

<div class="mb-2">
    <label>Overdeck Tangan</label>

    <select
        name="overdeck_worker_id"
        class="form-select"
        required
    >
        @foreach($overdeck_hands as $worker)
            <option
                value="{{ $worker->id }}"
                {{ optional($overdeckProcess)->worker_id == $worker->id ? 'selected' : '' }}
            >
                {{ $worker->name }}
                - Sisa Kerjaan {{ $worker->remaining_qty }} PCS
            </option>
        @endforeach
    </select>
</div>

<hr>
<h6>Detail Produk</h6>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Prioritas</th>
            <th>Product Variant</th>
            <th>Sisa KG</th>
            <th>Berat Ambil (KG)</th>
        </tr>
    </thead>

    <tbody id="productRows{{ $item->id }}">
        @foreach($item->details as $detail)
            <tr>
                <td>

                    {{ $detail->productionPlanningItem?->priority_order }}

                    <input
                        type="hidden"
                        name="planning_item_ids[]"
                        value="{{ $detail->production_planning_item_id }}"
                    >

                </td>

                <td>
                    <input
                        type="text"
                        class="form-control product-name"

                        value="{{ $detail->productionPlanningItem?->productVariant?->product?->name ?? '-' }} - {{ $detail->productionPlanningItem?->productVariant?->size ?? '-' }}"

                        readonly
                    >
                </td>

                <td>
                    <input
                        type="number"
                        class="form-control remaining-kg"

                       value="{{
                            ($detail->productionPlanningItem?->remaining_kg ?? 0)
                            +
                            $detail->weight
                            -
                            $detail->weight
                        }}"

                        readonly
                    >
                </td>

                <td>
                    <input
                        type="number"
                        step="0.01"
                        name="weights[]"
                        value="{{ $detail->weight }}"
                        class="form-control"
                        required
                    >
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>

        <tr>

            <th colspan="3"
                class="text-end">

                Total KG

            </th>

            <th>

                {{ $item->details->sum('weight') }}

                KG

            </th>

        </tr>

    </tfoot>
</table>

@endcomponent
@endforeach

{{-- ================= CREATE MODAL ================= --}}
@component('components.crud.create-modal', [
    'modalId' => 'createModal',
    'action'  => route('rawMaterial.store'),
    'title'   => 'Tambah Produksi Tahap 1'
])

<div class="mb-2">
    <label>Kurir</label>

    <input
        type="text"
        class="form-control"
        value="{{ $couriers->first()?->name ?? 'Tidak ada kurir tersedia' }}"
        readonly
    >

    <input
        type="hidden"
        name="courier_id"
        value="{{ $couriers->first()?->id }}"
    >
</div>

<div class="mb-2">
    <label for="date">Tanggal Produksi</label>

    <input
        type="date"
        class="form-control"
        id="date"
        name="date"
        value="{{ old('date', date('Y-m-d')) }}"
    >
</div>
<hr>
<h6>Divisi Worker</h6>

<div class="mb-2">
    <label>Pemotong</label>

    <select
        name="cutter_worker_id"
        class="form-select"
        required
    >
        @foreach($cutters as $worker)
            <option
                value="{{ $worker->id }}"
                {{ $loop->first ? 'selected' : '' }}
            >
                {{ $worker->name }} - Sisa Kerjaan {{ $worker->remaining_qty }} PCS
            </option>
        @endforeach
    </select>
</div>

<div class="mb-2">
    <label>Overdeck Tangan</label>

    <select
        name="overdeck_worker_id"
        class="form-select"
        required
    >
        @foreach($overdeck_hands as $worker)
            <option
                value="{{ $worker->id }}"
                {{ $loop->first ? 'selected' : '' }}
            >
                {{ $worker->name }} - Sisa Kerjaan {{ $worker->remaining_qty }} PCS
            </option>
        @endforeach
    </select>
</div>

<hr>
<h6>Detail Produk</h6>
<div class="table-responsive">
    <table
        class="table table-bordered"
        style="table-layout: auto;"
    >
        <thead>
            <tr>
                <th>Urutan</th>
                <th>Product Variant</th>
                <th>Sisa KG</th>
                <th>Berat Ambil (KG)</th>
            </tr>
        </thead>

        <tbody>

        @foreach($generatedTaskItems as $item)

        <tr>

            <td>

                {{ $item['priority_order'] }}

                <input
                    type="hidden"
                    name="planning_item_ids[]"
                    value="{{ $item['planning_item_id'] }}"
                >

            </td>

            <td>

                {{ $item['product_name'] }}
                -
                {{ $item['size'] }}

            </td>

            <td>

                {{ $item['remaining_kg'] }}

            </td>

            <td>

                <input
                    type="number"
                    name="weights[]"
                    value="{{ $item['take_kg'] }}"
                    class="form-control"
                    readonly
                >

            </td>

        </tr>

        @endforeach

        </tbody>
        <tfoot>

            <tr>

                <th colspan="3"
                    class="text-end">

                    Total KG

                </th>

                <th>

                     {{ collect($generatedTaskItems)->sum('take_kg') }}

                    KG

                </th>

            </tr>

        </tfoot>
    </table>
</div>

@endcomponent
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function () {
            let form = this.closest('.delete-form');

            Swal.fire({
                title: 'Yakin hapus?',
                text: 'Data tidak bisa dikembalikan!',
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
document.addEventListener('click', async function(e) {

    if (
        e.target.classList.contains('btn-lihat')
    ) {

        let id =
            e.target.dataset.id;

        let modalBody =
            document.getElementById(
                'modalDetailBody'
            );

        modalBody.innerHTML =
            'Loading...';

        try {

            let response =
                await fetch(`/rawMaterial/${id}`)

            let data =
                await response.json();

            let html = '';

            data.tracking.forEach(detail => {

                html += `
                    <div class="card mb-3">

                        <div class="card-header">

                            <b>
                                ${detail.product}
                            </b>

                            -
                            ${detail.size}

                        </div>

                        <div class="card-body">

                            <table class="table table-bordered">

                                <tr>
                                    <th width="30%">
                                        Proses Saat Ini
                                    </th>

                                    <td>
                                        ${
                                            detail.current_process?.stage
                                            ?? '-'
                                        }
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Pekerja
                                    </th>

                                    <td>
                                        ${
                                            detail.current_process?.worker
                                            ?? '-'
                                        }
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Tahap Ke
                                    </th>

                                    <td>
                                        ${
                                            detail.current_process?.sequence
                                            ?? '-'
                                        }
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Proses Selanjutnya
                                    </th>

                                    <td>
                                        ${
                                            detail.next_process?.stage
                                            ?? 'Selesai'
                                        }
                                    </td>
                                </tr>

                            </table>

                        </div>

                    </div>
                `;
            });

            modalBody.innerHTML =
                html;

        } catch(error) {

            modalBody.innerHTML =
                `
                    <div class="alert alert-danger">
                        Gagal load data
                    </div>
                `;

            console.log(error);
        }
    }
});
</script>
@endpush