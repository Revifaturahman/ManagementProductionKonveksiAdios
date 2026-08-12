@extends('layouts.master')

@section('content')

<div class="container-fluid">

    <div class="mb-4">
        <h3 class="mb-0">Halaman Depan</h3>
        <small class="text-muted">
            Ringkasan kondisi produksi dan persediaan
        </small>
    </div>

    {{-- KPI --}}
    <div class="row g-3 mb-4">

        <div class="col-xl col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-center">Bahan Baku</div>
                    <h4 class="mb-0 text-center">
                        {{ number_format($rawMaterialKg, 0, ',', '.') }} KG
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-center"> Stok Produk Setengah Jadi</div>
                    <h4 class="mb-0 text-center">
                        {{ number_format($semiProductPcs, 0, ',', '.') }} PCS
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-center">Stok Produk Jadi</div>
                    <h4 class="mb-0 text-center">
                        {{ number_format($finishedProductPcs, 0, ',', '.') }} PCS
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-center">Produk Menipis</div>
                    <h4 class="mb-0 text-danger text-center">
                        {{ $lowStocks->count() }}
                    </h4>
                </div>
            </div>
        </div>

    </div>

    {{-- KPI OPERASIONAL --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-center">Produksi Aktif</div>
                    <h4 class="mb-0 text-center">
                        {{ $activeProduction }} Produksi
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-center">Maklun Aktif</div>
                    <h4 class="mb-0 text-center">
                        {{ $activeWorkersProduction }} Orang
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-center">Kurir Aktif</div>
                    <h4 class="mb-0 text-center">
                        {{ $activeWorkers }} Orang
                    </h4>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        {{-- LOW STOCK --}}
        <div class="col-lg-12 mb-4">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white">
                    <strong>Produk Stok Menipis</strong>
                </div>

                <div class="card-body p-0">

                    <table class="table table-hover mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Size</th>
                                <th>Stock</th>
                                <th>Minimum</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($lowStockProducts as $item)

                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->size }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ $item->minimum_stock }}</td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="4"
                                        class="text-center text-muted py-4">
                                        Tidak ada produk menipis
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

</div>

@endsection