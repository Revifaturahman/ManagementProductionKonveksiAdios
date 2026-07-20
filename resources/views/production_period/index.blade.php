@extends('layouts.master')

@section('content')

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

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Periode Produksi</h5>

        <button class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#periodModal"
                onclick="openCreateModal()">

            Tambah Periode

        </button>
    </div>

    <div class="card-body">

        <table class="table table-bordered">

            <thead>

                <tr>

                    <th width="50">No</th>

                    <th>Kode</th>

                    <th>Periode</th>

                    <th width="120">Status</th>

                    <th width="220">Aksi</th>

                </tr>

            </thead>

            <tbody>

                @forelse($periods as $period)

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            {{ $period->code }}
                        </td>

                        <td>

                            {{ \Carbon\Carbon::parse($period->start_date)->format('d M Y') }}

                            s/d

                            {{ \Carbon\Carbon::parse($period->end_date)->format('d M Y') }}

                        </td>

                        <td>

                            @if($period->status == 'pending')

                                <span class="badge bg-warning text-dark">
                                    Pending
                                </span>

                            @elseif($period->status == 'active')

                                <span class="badge bg-success">
                                    Aktif
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    Selesai
                                </span>

                            @endif

                        </td>

                        <td class="text-nowrap">

                            <button class="btn btn-warning btn-sm"

                                    data-bs-toggle="modal"
                                    data-bs-target="#periodModal"

                                    onclick="openEditModal(
                                        '{{ $period->id }}',
                                        '{{ $period->start_date }}',
                                        '{{ $period->end_date }}',
                                        '{{ $period->status }}'
                                    )">

                                Edit

                            </button>

                            <form action="{{ route('production_period.destroy', $period->id) }}"
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

                        <td colspan="5"
                            class="text-center">

                            Data periode kosong

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>
</div>
<div class="modal fade"
     id="periodModal"
     tabindex="-1">

    <div class="modal-dialog">

        <form id="periodForm"
              method="POST"
              class="modal-content">

            @csrf

            <div id="methodField"></div>

            <div class="modal-header">

                <h5 class="modal-title"
                    id="periodModalLabel">

                    Tambah Periode

                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="mb-3">

                    <label>Tanggal Mulai</label>

                    <input type="date"
                           name="start_date"
                           id="start_date"
                           class="form-control"
                           required>

                </div>

                <div class="mb-3">

                    <label>Tanggal Selesai</label>

                    <input type="date"
                           name="end_date"
                           id="end_date"
                           class="form-control"
                           required>

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

function openCreateModal() {

    document.getElementById('periodModalLabel').innerText = 'Tambah Periode';

    document.getElementById('periodForm').action =
        "{{ route('production_period.store') }}";

    document.getElementById('methodField').innerHTML = '';

    document.getElementById('start_date').value = '';

    document.getElementById('end_date').value = '';
}

function openEditModal(id, startDate, endDate) {

    document.getElementById('periodModalLabel').innerText = 'Edit Periode';

    document.getElementById('periodForm').action =
        `/production_period/${id}`;

    document.getElementById('methodField').innerHTML =
        '@method("PUT")';

    document.getElementById('start_date').value = startDate;

    document.getElementById('end_date').value = endDate;
}

document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.btn-delete').forEach(button => {

        button.addEventListener('click', function () {

            let form = this.closest('.delete-form');

            Swal.fire({
                title: 'Yakin hapus?',
                text: 'Data periode produksi akan dihapus!',
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