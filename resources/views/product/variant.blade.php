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
<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#obrasModal" onclick="openCreateModal()">Tambah Varian Produk</button>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle">

        <thead class="table-light">
            <tr>
                <th width="80">No</th>
                <th>Produk</th>
                <th>Ukuran</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($variants as $variant)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $variant->product->name }}</td>

                    <td>
                        <span class="badge bg-info">
                            {{ $variant->size }}
                        </span>
                    </td>

                    <td>

                        <button
                            type="button"
                            class="btn btn-sm btn-warning"
                            onclick="openEditModal(
                                {{ $variant->id }},
                                '{{ $variant->size }}',
                                {{ $variant->product_id }}
                            )">

                            <i class="bi bi-pencil"></i>

                        </button>

                        <form
                            action="{{ route('variant.destroy', $variant->id) }}"
                            method="POST"
                            class="delete-form d-inline">

                            @csrf
                            @method('DELETE')

                            <button
                                type="button"
                                class="btn btn-sm btn-danger btn-delete">

                                <i class="bi bi-trash"></i>

                            </button>
                        </form>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="4" class="text-center">
                        Tidak ada data varian produk
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>
</div>


<!-- Modal -->
<div class="modal fade" id="obrasModal" tabindex="-1" aria-labelledby="obrasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('variant.store') }}" method="POST" class="modal-content" autocomplete="off">
            @csrf
            <input type="hidden" name="variant_id" id="variant_id">

            <div class="modal-header">
                <h5 class="modal-title" id="obrasModalLabel">Tambah Varian Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-danger text-center">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select class="form-select" name="product_id" id="product_id" value="{{ old('product_id') }}">
                        {{-- <option selected disabled>Pilih Kategori</option> --}}
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ukuran</label>
                    <select class="form-select" name="size" id="size">
                        <option value="S" selected>S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
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
    document.getElementById('obrasModalLabel').innerText = 'Tambah Varian Produk';
    document.getElementById('variant_id').value = '';
    document.getElementById('product_id').selectedIndex = 0;
    document.getElementById('size').value = 'S';
}

function openEditModal(id, name, category) {
    console.log(id, name, category);
    document.getElementById('obrasModalLabel').innerText = 'Edit Varian Produk';
    document.getElementById('variant_id').value = id;
    document.getElementById('size').value = name;
    document.getElementById('product_id').value = category;

    var modal = new bootstrap.Modal(document.getElementById('obrasModal'));
    modal.show();
}

document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.btn-delete')
        .forEach(button => {

            button.addEventListener('click', function () {

                let form = this.closest('.delete-form');

                Swal.fire({

                    title: 'Yakin hapus?',
                    text: 'Data varian produk akan dihapus!',
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
