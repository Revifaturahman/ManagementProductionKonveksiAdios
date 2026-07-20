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
    });
</script>
@endif
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#obrasModal" onclick="openCreateModal()">Tambah Produk</button>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">

        <thead class="table-light">
            <tr>
                <th width="80">No</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($products as $product)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $product->name }}</td>

                    <td>
                        <span class="badge bg-info">
                            {{ $product->category->name }}
                        </span>
                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn btn-sm btn-warning"
                            onclick="openEditModal(
                                {{ $product->id }},
                                '{{ $product->name }}',
                                '{{ $product->category_id }}'
                            )">

                            <i class="bi bi-pencil"></i>

                        </button>

                        <form
                            action="{{ route('product.destroy', $product->id) }}"
                            method="POST"
                            class="d-inline">

                            @csrf
                            @method('DELETE')

                            <button
                                type="button"
                                class="btn btn-sm btn-danger"
                                onclick="confirmDelete(this.form)">

                                <i class="bi bi-trash"></i>

                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="4" class="text-center">
                        Tidak ada data produk
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>
</div>


<!-- Modal -->
<div class="modal fade" id="obrasModal" tabindex="-1" aria-labelledby="obrasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('product.store') }}" method="POST" class="modal-content" autocomplete="off">
            @csrf
            <input type="hidden" name="id" id="product_id">

            <div class="modal-header">
                <h5 class="modal-title" id="obrasModalLabel">Tambah Produk</h5>
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
                    <label class="form-label">Kategori</label>
                    <select class="form-select" name="category" id="category">
                        {{-- <option selected disabled>Pilih Kategori</option> --}}
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
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
    document.getElementById('obrasModalLabel').innerText = 'Tambah Produk';
    document.getElementById('product_id').value = '';
    document.getElementById('name').value = '';
    document.getElementById('category').value = '';
}

function openEditModal(id, name, category) {
    document.getElementById('obrasModalLabel').innerText = 'Edit Produk';
    document.getElementById('product_id').value = id;
    document.getElementById('name').value = name;
    document.getElementById('category').value = category;

    var modal = new bootstrap.Modal(document.getElementById('obrasModal'));
    modal.show();
}

function confirmDelete(form) {
    if (confirm("Yakin ingin menghapus Produk ini?")) {
        form.submit();
    }
}

</script>
@endsection
