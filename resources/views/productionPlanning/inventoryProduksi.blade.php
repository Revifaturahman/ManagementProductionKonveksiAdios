@extends('layouts.master')

@section('content')

<div class="container">

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>

        </div>
    @endif

    {{-- ERROR DARI LOGIC --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>

        </div>
    @endif

    {{-- ERROR VALIDASI --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">

            {{ $errors->first() }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>

        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h4 class="mb-0">
            Persediaan Stok Produk
        </h4>

        <button
            form="inventory-form"
            type="submit"
            class="btn btn-primary">

            Simpan Semua Perubahan

        </button>

    </div>

    <form id="inventory-form"
          action="{{ route('production_inventory.bulk_update') }}"
          method="POST">

        @csrf
        @method('PUT')

        <div class="table-responsive">

            <table class="table table-bordered align-middle">

                <thead class="table-light">

                    <tr>

                        <th width="50">
                            No
                        </th>

                        <th>
                            Produk
                        </th>

                        <th width="120">
                            Size
                        </th>

                        <th width="160">
                            Semi Product
                        </th>

                        <th width="160">
                            Finished Product
                        </th>

                        <th width="140">
                            Total Stock
                        </th>

                        <th width="160">
                            Minimum Stock
                        </th>

                        <th width="150">
                            Status
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($variants as $variant)

                        @php

                            $semi =
                                $variant->semiProduct->qty ?? 0;

                            $finished =
                                $variant->finishedProduct->qty ?? 0;

                            $total =
                                $semi + $finished;

                        @endphp

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                {{ $variant->product->name }}
                            </td>

                            <td>
                                {{ $variant->size }}
                            </td>

                            <td>

                                <input type="number"
                                       class="form-control form-control-sm"
                                       name="variants[{{ $variant->id }}][semi_qty]"
                                       value="{{ $semi }}">

                            </td>

                            <td>

                                <input type="number"
                                       class="form-control form-control-sm"
                                       name="variants[{{ $variant->id }}][finished_qty]"
                                       value="{{ $finished }}">

                            </td>

                            <td>

                                <strong>

                                    {{ $total }}

                                </strong>

                            </td>

                            <td>

                                <input type="number"
                                       class="form-control form-control-sm"
                                       name="variants[{{ $variant->id }}][minimum_stock]"
                                       value="{{ $variant->minimum_stock }}">

                            </td>

                            <td>

                                @if($total <= 0)

                                    <span class="badge bg-danger">

                                        Stock Habis

                                    </span>

                                @elseif($total <= $variant->minimum_stock)

                                    <span class="badge bg-warning text-dark">

                                        Stock Menipis

                                    </span>

                                @else

                                    <span class="badge bg-success">

                                        Stock Aman

                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8"
                                class="text-center">

                                Data persediaan kosong

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </form>

</div>

@endsection