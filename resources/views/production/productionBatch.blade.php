@extends('layouts.master')

@section('content')

<div class="container">
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul>
            @foreach ($errors->all() as $error)
                {{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            @endforeach
        </ul>
    </div>
@endif
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
    <h4 class="mb-3">Produksi Tahap 2</h4>
    <form method="GET">

        <div class="row mb-3">

            <div class="col-md-3">

                <label>Dari Tanggal</label>

                <input
                    type="date"
                    name="start_date"
                    class="form-control"
                    value="{{ request('start_date') }}"
                >

            </div>

            <div class="col-md-3">

                <label>Sampai Tanggal</label>

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

    </form>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createModal">
        Tambah Produksi Tahap 2
    </button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kurir</th>
                <th>Jenis Proses</th>
                <th>Produk</th>
                <th>Size</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
        @foreach($productionBatches as $batch)
            <tr>
                <td>{{ $batch->courier->name }}</td>
                <td>
                    {{ ucfirst($batch->type) }}
                </td>

                <td>
                    @foreach ($batch->details as $detail)
                        {{ $detail->variant->product->name }} <br>
                    @endforeach
                </td>

                <td>
                    @foreach ($batch->details as $detail)
                        {{ $detail->variant->size }} <br>
                    @endforeach
                </td>

                <td>
                    @foreach ($batch->details as $detail)
                        {{ $detail->qty }} <br>
                    @endforeach
                </td>
                <td>{{ \Carbon\Carbon::parse($batch->date)->translatedFormat('d F Y') }}</td>

                <td>
                    <span class="badge bg-info">{{ $batch->status }}</span>
                </td>

                <td>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $batch->id }}">
                        Edit
                    </button>

                    @include('components.crud.delete-button', [
                        'action' => route('productionBatch.destroy', $batch->id)
                    ])
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>

{{-- ================= EDIT MODALS ================= --}}
@foreach($productionBatches as $item)
@component('components.crud.edit-modal', [
    'modalId' => 'editModal'.$item->id,
    'action'  => route('productionBatch.update', $item->id),
    'title'   => 'Edit Production Batch'
])

<div class="mb-2">
    <label>Kurir</label>

    <input
        type="text"
        class="form-control"
        value="{{ optional($couriers->first())->name ?? 'Tidak ada kurir tersedia' }}"
        readonly
    >

    <input
        type="hidden"
        name="courier_id"
        value="{{ optional($couriers->first())->id }}"
    >
</div>

<div class="mb-2">
    <label>Tanggal Produksi</label>

    {{-- Tampilan Indonesia --}}
    <input
        type="text"
        class="form-control mb-2"
        value="{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}"
        readonly
    >

    {{-- Value asli untuk submit --}}
    <input
        type="hidden"
        name="date"
        value="{{ old('date', date('Y-m-d')) }}"
    >
</div>

<div class="mb-2">
    <label>Jenis Product</label>

    <input
        type="text"
        class="form-control"
        value="{{ ucfirst($item->type) }}"
        readonly
    >

    <input
        type="hidden"
        name="type"
        value="{{ $item->type }}"
    >
</div>

<div
    id="workerFields{{ $item->id }}"

    data-obras="{{ optional(
        $item->details->first()?->processes
            ->where('stage', 'obras')
            ->first()
    )->worker_id }}"

    data-penjahit="{{ optional(
        $item->details->first()?->processes
            ->where('stage', 'penjahit')
            ->first()
    )->worker_id }}"

    data-obras2="{{ optional(
        $item->details->first()?->processes
            ->where('stage', 'obras2')
            ->first()
    )->worker_id }}"

    data-penjahit2="{{ optional(
        $item->details->first()?->processes
            ->where('stage', 'penjahit2')
            ->first()
    )->worker_id }}"

    data-overdek="{{ optional(
        $item->details->first()?->processes
            ->where('stage', 'overdeck_bawah')
            ->first()
    )->worker_id }}"
></div>

<hr>
<h6>Detail Produk</h6>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Product</th>
            <th>Size</th>
            <th>Qty</th>
        </tr>
    </thead>

    <tbody>

        @foreach($item->details as $detail)

        <tr>

            <td>
                {{ $loop->iteration }}
            </td>

            <td>

                {{ $detail->variant->product->name }}

                <input
                    type="hidden"
                    name="product_variant_ids[]"
                    value="{{ $detail->product_variant_id }}"
                >

            </td>

            <td>
                {{ $detail->variant->size }}
            </td>

            <td>

                <input
                    type="number"
                    name="qty[]"
                    value="{{ $detail->qty }}"
                    class="form-control"
                    readonly
                >

            </td>

        </tr>

        @endforeach

    </tbody>
</table>

@endcomponent
@endforeach

{{-- ================= CREATE MODAL ================= --}}
@component('components.crud.create-modal', [
    'modalId' => 'createModal',
    'action'  => route('productionBatch.store'),
    'title'   => 'Tambah Produksi'
])
{{-- @if (session('error_id') == $item->id)
<script>
document.addEventListener("DOMContentLoaded", function () {
    let modal = new bootstrap.Modal(document.getElementById('editModal{{ $item->id }}'));
    modal.show();
});
</script>
@endif --}}

<div class="mb-2">
    <label>Kurir</label>

    <input
        type="text"
        class="form-control"
        value="{{ optional($couriers->first())->name ?? 'Tidak ada kurir tersedia' }}"
        readonly
    >

    <input
        type="hidden"
        name="courier_id"
        value="{{ optional($couriers->first())->id }}"
    >
</div>

<div class="mb-2">
    <label>Tanggal Produksi</label>

    {{-- Tampilan Indonesia --}}
    <input
        type="text"
        class="form-control mb-2"
        value="{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}"
        readonly
    >

    {{-- Value asli untuk submit --}}
    <input
        type="hidden"
        name="date"
        value="{{ old('date', date('Y-m-d')) }}"
    >
</div>

<div class="mb-2">
    <label>Jenis Product</label>

    <input
        type="text"
        class="form-control"
        value="{{ ucfirst($suggestions['type']) }}"
        readonly
    >

    <input
        type="hidden"
        name="type"
        value="{{ $suggestions['type'] }}"
    >
</div>

<input
    type="hidden"
    name="type"
    value="{{ $suggestions['type'] }}"
>

<div id="workerFields"></div>

<hr>
<h6>Detail Produk</h6>

<table class="table table-bordered">

    <thead>

        <tr>

            <th>Prioritas</th>

            <th>Product</th>

            <th>Size</th>

            <th>Stok Produk Setengah Jadi</th>

            <th>Ambil</th>

            <th>Sisa</th>

        </tr>

    </thead>

    <tbody>

        @foreach($suggestions['items'] as $item)

        <tr>

            <td>

                {{ $item['priority_order'] }}

                <input
                    type="hidden"
                    name="product_variant_ids[]"
                    value="{{ $item['product_variant_id'] }}"
                >

            </td>

            <td>

                {{ $item['product_name'] }}

            </td>

            <td>

                {{ $item['size'] }}

            </td>

            <td>
                {{ $item['semi_qty'] }}
            </td>

            <td>

                <input
                    type="number"
                    name="qty[]"
                    value="{{ $item['take_qty'] }}"
                    class="form-control"
                    readonly
                >

            </td>

            <td>

                {{
                    $item['semi_qty']
                    -
                    $item['take_qty']
                }}

            </td>

        </tr>

        @endforeach

    </tbody>

    <tfoot>

        <tr>

            <th colspan="5"
                class="text-start">

                Total PCS

            </th>

            <th>

                {{ collect($suggestions['items'])->sum('take_qty') }}

            </th>

        </tr>

    </tfoot>

</table>

@endcomponent

@endsection

@push('scripts')
<script>
function filterWorker(type = null, id = null) {

    let obras1 =
        @json($obras1Workers);

    let obras2 =
        @json($obras2Workers);
    let tailor1 =
        @json($tailor1Workers);

    let tailor2 =
        @json($tailor2Workers);
    let overdeckBawah = @json($overdeck_bottoms);

    console.log('=== Tailor 1 ===');
    console.table(
    tailor1.map(worker => ({
        nama: worker.name,
        activeType: worker.active_type,
        next: worker.next_process
    }))


);

    let container;

    if (id) {
        container = document.getElementById(
            'workerFields' + id
        );
    } else {
        container = document.getElementById(
            'workerFields'
        );
    }

    if (!container) return;

    container.innerHTML = '';

    // jika type kosong tidak menampilkan worker
    if (!type) return;

    let fields = [];

    // OBLONG
    if (type === 'oblong') {

        fields = [

            {
                label: 'Obras',
                name: 'obras_id',
                workers: obras1
            },

            {
                label: 'Penjahit',
                name: 'penjahit_id',
                workers: tailor1
            },

            {
                label: 'Obras 2',
                name: 'obras2_id',
                workers: obras2
            },

            {
                label: 'Overdeck Bawah',
                name: 'overdek_id',
                workers: overdeckBawah
            },
        ];
    }

    // BERKERAH
    else if (type === 'berkerah') {

        fields = [

            {
                label: 'Penjahit',
                name: 'penjahit_id',
                workers: tailor1
            },

            {
                label: 'Obras',
                name: 'obras_id',
                workers: obras1
            },

            {
                label: 'Penjahit 2',
                name: 'penjahit2_id',
                workers: tailor2
            },

            {
                label: 'Overdeck Bawah',
                name: 'overdek_id',
                workers: overdeckBawah
            },
        ];
    }

    fields.forEach(field => {

        let options =
            `<option value="">- pilih pekerja -</option>`;

        let selectedWorker =
            container.dataset[
                field.name.replace(
                    '_id',
                    ''
                )
            ];

        let filteredWorkers = field.workers.filter(worker => {

            console.log(
                field.name,
                worker.name,
                worker.active_type,
                worker.next_process
            );

            // default cocok jenis product
            let matchType =
                worker.active_type === null ||
                worker.active_type === type;

            if (!matchType) return false;

            // ================= OBLONG =================
            if (type === 'oblong') {

                if (field.name === 'obras_id') {
                    return (
                        worker.next_process === null ||
                        worker.next_process === 'penjahitan'
                    );
                }

                if (field.name === 'penjahit_id') {
                    return (
                        worker.next_process === null ||
                        worker.next_process === 'obras2'
                    );
                }

                if (field.name === 'obras2_id') {
                    return (
                        worker.next_process === null ||
                        worker.next_process === 'overdeck_bawah'
                    );
                }

                if (field.name === 'overdek_id') {
                    return true;
                }
            }

            // ================= BERKERAH =================
            if (type === 'berkerah') {

                if (field.name === 'penjahit_id') {
                    return (
                        worker.next_process === null ||
                        worker.next_process === 'obras'
                    );
                }

                if (field.name === 'obras_id') {
                    return (
                        worker.next_process === null ||
                        worker.next_process === 'penjahit2'
                    );
                }

                if (field.name === 'penjahit2_id') {
                    return (
                        worker.next_process === null ||
                        worker.next_process === 'overdeck_bawah'
                    );
                }

                if (field.name === 'overdek_id') {
                    return true;
                }
            }

            return false;
        });
        filteredWorkers.forEach(worker => {

            let selected =
                selectedWorker
                    ? (selectedWorker == worker.id ? 'selected' : '')
                    : (filteredWorkers[0]?.id == worker.id ? 'selected' : '');

            let activeProduct =
                worker.active_product
                    ? worker.active_product
                    : '-';

            let nextProcess =
                worker.next_process
                    ? worker.next_process
                    : '-';

            options += `
                <option
                    value="${worker.id}"
                    ${selected}
                >
                    ${worker.name}
                    | Product ${worker.active_type ?? '-'}
                    | Sisa ${worker.remaining_qty} pcs
                    | Next ${worker.next_process ?? '-'}
                </option>
            `;
        });

        container.innerHTML += `
            <div class="mb-2">

                <label>
                    ${field.label}
                </label>

                <select
                    name="${field.name}"
                    class="form-control"
                    required
                >
                    ${options}
                </select>

            </div>
        `;
    });
}
document.getElementById('createModal')
.addEventListener('shown.bs.modal', function () {

    filterWorker(
        "{{ $suggestions['type'] }}"
    );
});


// ================= EDIT MODAL =================
document.addEventListener("DOMContentLoaded", function () {

    @foreach($productionBatches as $item)

        filterWorker(
            '{{ $item->type }}',
            {{ $item->id }}
        );

    @endforeach
});
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
</script>
@endpush