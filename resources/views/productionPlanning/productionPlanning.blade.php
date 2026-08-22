@extends('layouts.master')

@section('content')

<div class="container">

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

    <div class="d-flex justify-content-between mb-3">

        <h4>Perencanaan Produksi</h4>

    </div>
    <div class="alert alert-info">
        <strong>Keterangan Konversi Bahan Baku:</strong>

        <ul class="mb-0 mt-2">
            <li>
                1 KG bahan baku =
                <strong>4 PCS</strong> Kaos Tangan Pendek Dewasa
            </li>

            <li>
                1 KG bahan baku =
                <strong>3 PCS</strong> Kaos Tangan Panjang Dewasa
            </li>

            <li>
                1 KG bahan baku =
                <strong>8 PCS</strong> Kaos Anak
            </li>
        </ul>
    </div>
    {{-- <form method="GET">

        <div class="row mb-3">

            <div class="col-md-4">

                <label>
                    Filter Periode
                </label>

                <select
                    name="production_period_id"
                    class="form-control"
                    onchange="this.form.submit()"
                >

                    <option value="">
                        Semua Periode
                    </option>

                    @foreach($periods as $period)

                        <option
                            value="{{ $period->id }}"
                            {{
                                request('production_period_id') == $period->id
                                    ? 'selected'
                                    : ''
                            }}
                        >

                            {{ $period->code }}
                            -
                            {{ $period->start_date }}
                            s/d
                            {{ $period->end_date }}

                        </option>

                    @endforeach

                </select>

            </div>

        </div>

    </form> --}}

    <button class="btn btn-primary mb-3"
                data-bs-toggle="modal"
                data-bs-target="#createModal">

            Tambah Perencanaan

    </button>

    <div class="container-fluid">

        <div class="table-responsive">

            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th>Kode Periode</th>
                        <th>Periode</th>
                        <th>Bahan Baku</th>
                        <th width="300">Products</th>
                        <th>Estimasi KG</th>
                        <th>Remaining KG</th>
                        <th>Estimasi PCS</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($production_plannings as $planning)

                    <tr>

                        <td>

                            {{ $planning->period->code }}

                        </td>

                        <td>
                            {{ $planning->period->start_date }}

                                s/d

                                {{ $planning->period->end_date }}
                        </td>

                        <td>
                            {{ $planning->rawMaterialMaster->name }}
                        </td>

                        <td>

                            @foreach($planning->items as $item)

                                {{ $item->productVariant->product->name }}
                                -
                                {{ $item->productVariant->size }}

                                <br>

                            @endforeach

                        </td>

                        <td>

                            @foreach($planning->items as $item)

                                {{ $item->estimated_kg }} KG

                                <br>

                            @endforeach

                        </td>

                        <td>

                            @foreach($planning->items as $item)

                                {{ $item->remaining_kg }} KG

                                <br>

                            @endforeach

                        </td>

                        <td>

                            @foreach($planning->items as $item)

                                {{ $item->estimated_qty }}

                                <br>

                            @endforeach

                        </td>

                        <td>
                            {{ $planning->items->sum('estimated_kg') }} KG
                        </td>

                        <td>

                            <span class="badge bg-info">
                                {{ $planning->status }}
                            </span>

                        </td>

                        <td>

                            <button class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal{{ $planning->id }}">

                                Edit

                            </button>

                            <form action="{{ route('production_planning.destroy', $planning->id) }}"
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

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>

{{-- CREATE MODAL --}}
<div class="modal fade"
     id="createModal"
     tabindex="-1">

    <div class="modal-dialog modal-lg">

        <form action="{{ route('production_planning.store') }}"
              method="POST"
              class="modal-content">

            @csrf

            <div class="modal-header">

                <h5 class="modal-title">
                    Tambah Production Planning
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                {{-- HEADER --}}

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label>Periode Produksi</label>

                        <select
                            name="production_period_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                -- Pilih Periode --
                            </option>

                            @foreach($periods as $period)

                                <option
                                    value="{{ $period->id }}"
                                >

                                    {{ $period->code }}
                                    -
                                    {{ $period->start_date }}
                                    s/d
                                    {{ $period->end_date }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label>Bahan Baku</label>

                        <select name="raw_material_master_id"
                            class="form-control"
                            required>

                            <option value="">
                                -- pilih bahan baku --
                            </option>

                            @foreach($raw_material_masters as $material)

                                <option value="{{ $material->id }}">

                                    {{ $material->name }}
                                    -
                                    Stock:
                                    {{ $material->stock->stock_kg ?? 0 }} KG

                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="mb-3">

                    <label>Notes</label>

                    <textarea name="notes"
                              class="form-control"></textarea>

                </div>

                <hr>

                {{-- DETAIL ITEMS --}}

                <div class="d-flex justify-content-between mb-2">

                    <h6>Detail Produk</h6>

                    <button type="button"
                            id="btnGenerate"
                            class="btn btn-primary btn-sm">

                        Generate Prioritas

                    </button>

                </div>

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>Prioritas</th>
                            <th>Product Variant</th>
                            <th>Estimated KG</th>
                            <th>Esitimasi PCS</th>
                        </tr>
                    </thead>

                    <tbody id="planningRows">

                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-center">
                                Total
                            </th>

                            <th id="totalKgDisplay" class="text-center">
                                0 KG
                            </th>

                            <th id="totalPcsDisplay" class="text-center">
                                0 PCS
                            </th>
                        </tr>
                    </tfoot>

                </table>

            </div>

            <div class="modal-footer">

                <button type="submit"
                        class="btn btn-success">

                    Save

                </button>

            </div>

        </form>

    </div>

</div>

{{-- EDIT MODALS --}}
@foreach($production_plannings as $planning)

<div class="modal fade"
     id="editModal{{ $planning->id }}"
     tabindex="-1">

    <div class="modal-dialog modal-lg">

        <form action="{{ route('production_planning.update', $planning->id) }}"
              method="POST"
              class="modal-content">

            @csrf
            @method('PUT')

            <div class="modal-header">

                <h5 class="modal-title">
                    Edit Production Planning
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                {{-- HEADER --}}

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label>Periode Mulai</label>

                        <select
                            name="production_period_id"
                            class="form-control"
                            required
                        >

                            @foreach($periods as $period)

                                <option
                                    value="{{ $period->id }}"
                                    {{
                                        $planning->production_period_id ==
                                        $period->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{ $period->code }}
                                    -
                                    {{ $period->start_date }}
                                    s/d
                                    {{ $period->end_date }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label>Bahan Baku</label>

                        <select name="raw_material_master_id"
                                class="form-control"
                                required>

                            @foreach($raw_material_masters as $material)

                                <option value="{{ $material->id }}"
                                    {{ $planning->raw_material_master_id == $material->id ? 'selected' : '' }}>

                                    {{ $material->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

                <div class="mb-3">

                    <label>Notes</label>

                    <textarea name="notes"
                              class="form-control">{{ $planning->notes }}</textarea>

                </div>

                <hr>

                <h6>Detail Produk</h6>

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th>Prioritas</th>

                            <th>Product Variant</th>

                            <th>Estimated KG</th>

                            <th>Esitimasi PCS</th>


                        </tr>

                    </thead>

                    <tbody id="editRows{{ $planning->id }}">

                        @foreach($planning->items as $item)

                        <tr>

                            <td>

                                {{ $item->priority_order }}

                                <input type="hidden"
                                    name="priority_orders[]"
                                    value="{{ $item->priority_order }}">

                            </td>

                            <td>

                                <select name="product_variant_ids[]"
                                        class="form-control"
                                        required>

                                    @foreach($product_variants as $variant)

                                        <option value="{{ $variant->id }}"
                                            {{ $item->product_variant_id == $variant->id ? 'selected' : '' }}>

                                            {{ $variant->product->name }}
                                            -
                                            {{ $variant->size }}

                                        </option>

                                    @endforeach

                                </select>

                            </td>

                            <td>

                                <input type="number"
                                    step="0.01"
                                    name="estimated_kgs[]"
                                    value="{{ $item->estimated_kg }}"
                                    class="form-control edit-kg-input"
                                    data-planning="{{ $planning->id }}"
                                    required>

                            </td>

                            
                            <td>

                                <span class="edit-estimated-pcs">

                                    {{ $item->estimated_qty }}

                                </span>

                                <input
                                    type="hidden"
                                    class="ratio-per-kg"
                                    value="{{ $item->productVariant->product->ratio_per_kg }}"
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

                            <th id="editTotalKg{{ $planning->id }}">

                                {{ $planning->items->sum('estimated_kg') }} KG

                            </th>

                        </tr>

                    </tfoot>

                </table>
            </div>

            <div class="modal-footer">

                <button type="submit"
                        class="btn btn-success">

                    Update

                </button>

            </div>

        </form>

    </div>

</div>

@endforeach

@endsection

@push('scripts')

<script>
document.addEventListener(
    'input',
    function(e)
    {
        if (
            e.target.classList.contains(
                'edit-kg-input'
            )
        ) {

            const planningId =
                e.target.dataset.planning;

            let total = 0;

            document
                .querySelectorAll(
                    `.edit-kg-input[data-planning="${planningId}"]`
                )
                .forEach(input => {

                    total += parseFloat(
                        input.value || 0
                    );

                });

            document
                .getElementById(
                    `editTotalKg${planningId}`
                )
                .innerText =
                total + ' KG';

            let row =
                e.target.closest('tr');

            let kg =
                parseFloat(
                    e.target.value || 0
                );

            let ratio =
                parseFloat(
                    row.querySelector(
                        '.ratio-per-kg'
                    ).value
                );

            row.querySelector(
                '.edit-estimated-pcs'
            ).innerText =
                Math.round(
                    kg * ratio
                );
        }
    }
);

document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.btn-delete')
        .forEach(button => {

        button.addEventListener('click', function () {

            let form =
                this.closest('.delete-form');

            Swal.fire({

                title: 'Yakin hapus?',
                text: 'Data planning akan dihapus!',
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

// Generate Prioritas button click handler

function renderSuggestions(data)
{
    let tbody =
        document.getElementById(
            'planningRows'
        );

    tbody.innerHTML = '';

    data.forEach(item => {

        tbody.insertAdjacentHTML(
            'beforeend',
            `
            <tr>

                <td>

                    ${item.priority_order}

                    <input type="hidden"
                           name="priority_orders[]"
                           value="${item.priority_order}">

                </td>

                <td>

                    ${item.product_name}
                    -
                    ${item.size}

                    <input type="hidden"
                           name="product_variant_ids[]"
                           value="${item.product_variant_id}">

                </td>

                <td>

                    <input type="number"
                           step="0.01"
                           min="1"
                           name="estimated_kgs[]"
                           value="${item.suggested_kg}"
                           class="form-control estimated-kg-input"
                           required>

                </td>

                
                <td>

                    <span class="estimated-pcs">

                        ${item.suggested_qty}

                    </span>

                    <input
                        type="hidden"
                        class="ratio-per-kg"
                        value="${item.ratio_per_kg}"
                    >

                </td>

            </tr>
            `
        );

    });
    updateTotalKg();
}

document
.getElementById('btnGenerate')
.addEventListener('click', async function () {

    const btn = this;

    btn.disabled = true;

    btn.innerHTML =
        'Generating...';

    try {

        let rawMaterialId =
            document.querySelector(
                '[name="raw_material_master_id"]'
            ).value;

        if (!rawMaterialId) {

            Swal.fire(
                'Pilih bahan baku terlebih dahulu'
            );

            return;
        }

        let response =
            await fetch(
                '/production-planning/generate-suggestions',
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json',

                        'X-CSRF-TOKEN':
                            '{{ csrf_token() }}'
                    },

                    body: JSON.stringify({
                        raw_material_master_id:
                            rawMaterialId
                    })
                }
            );

        let result =
            await response.json();

        renderSuggestions(
            result.data
        );

    } finally {

        btn.disabled = false;

        btn.innerHTML =
            'Generate Prioritas';
    }
});

function updateTotalKg()
{
    let totalKg = 0;
    let totalPcs = 0;

    let rows = document
        .querySelector('#planningRows')
        .querySelectorAll('tr');

    rows.forEach(row => {

        let kgInput = row.querySelector(
            'input[name="estimated_kgs[]"]'
        );

        let ratioInput = row.querySelector(
            '.ratio-per-kg'
        );

        if (!kgInput || !ratioInput) {
            return;
        }

        let kg = parseFloat(
            kgInput.value || 0
        );

        let ratio = parseFloat(
            ratioInput.value || 0
        );

        totalKg += kg;

        totalPcs += Math.round(
            kg * ratio
        );

    });

    document.getElementById(
        'totalKgDisplay'
    ).innerText =
        totalKg + ' KG';

    document.getElementById(
        'totalPcsDisplay'
    ).innerText =
        totalPcs + ' PCS';
}

document.addEventListener(
    'input',
    function(e)
    {
        if (
            e.target.name ===
            'estimated_kgs[]'
        ) {

            updateTotalKg();

        }
    }
);

document.addEventListener(
    'input',
    function(e)
    {
        if (
            e.target.classList.contains(
                'estimated-kg-input'
            )
        ) {

            let row =
                e.target.closest('tr');

            let kg =
                parseFloat(
                    e.target.value || 0
                );

            let ratio =
                parseFloat(
                    row.querySelector(
                        '.ratio-per-kg'
                    ).value
                );

            let pcs =
                Math.round(
                    kg * ratio
                );

            row.querySelector(
                '.estimated-pcs'
            ).innerText =
                pcs;

            updateTotalKg();
        }
    }
);
</script>

@endpush