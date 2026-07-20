@extends('layouts.master')

@section('content')

<div class="container">

    <div class="d-flex justify-content-between mb-3">
        <h4>Stok Produk</h4>
    </div>

    @if($lastOpname)

    <div class="alert alert-info">

        <strong>
            Informasi Stok
        </strong>

        <br>

        Data stok berdasarkan
        stok opname terakhir pada:

        <strong>
            {{ \Carbon\Carbon::parse($lastOpname)
                ->format('d F Y H:i') }}
        </strong>

    </div>

    @endif

    <div class="table-responsive">
        <table class="table table-bordered align-middle">

            <thead class="table-light">
                <tr>
                    <th>Produk</th>
                    <th width="200">Total Keseluruhan</th>
                </tr>
            </thead>

            <tbody>

                @foreach($inventory as $productId => $data)

                    {{-- PRODUCT --}}
                    <tr
                        data-bs-toggle="collapse"
                        data-bs-target="#product{{ $productId }}"
                        style="cursor:pointer">

                        <td class="fw-semibold">

                            <i class="bi bi-chevron-right me-2"></i>

                            {{ $data['product']->name }}

                        </td>

                        <td class="fw-semibold">
                            {{ $data['total_qty'] }}
                        </td>

                    </tr>

                    {{-- PRODUCT DETAIL --}}
                    <tr>

                        <td colspan="2" class="p-0 border-0">

                            <div class="collapse" id="product{{ $productId }}">

                                {{-- SEMI PRODUCT --}}
                                <div
                                    class="row g-0 py-2 border-top"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#semi{{ $productId }}"
                                    style="cursor:pointer">

                                    <div class="col-8 ps-4">

                                        <i class="bi bi-chevron-right me-2"></i>

                                        <strong>Produk Setengah Jadi</strong>

                                    </div>

                                    <div class="col-4 fw-semibold">

                                        {{ $data['semi_products']->sum('qty') }}

                                    </div>

                                </div>

                                <div class="collapse" id="semi{{ $productId }}">

                                    @foreach($data['semi_products'] as $item)

                                        <div class="row g-0 py-2 border-top">

                                            <div class="col-8 ps-5">

                                                {{ $item->variant->size }}

                                            </div>

                                            <div class="col-4">

                                                {{ $item->qty }}

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                                {{-- FINISHED PRODUCT --}}
                                <div
                                    class="row g-0 py-2 border-top"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#finished{{ $productId }}"
                                    style="cursor:pointer">

                                    <div class="col-8 ps-4">

                                        <i class="bi bi-chevron-right me-2"></i>

                                        <strong>Produk Jadi</strong>

                                    </div>

                                    <div class="col-4 fw-semibold">

                                        {{ $data['finished_products']->sum('qty') }}

                                    </div>

                                </div>

                                <div class="collapse" id="finished{{ $productId }}">

                                    @foreach($data['finished_products'] as $item)

                                        <div class="row g-0 py-2 border-top">

                                            <div class="col-8 ps-5">

                                                {{ $item->variant->size }}

                                            </div>

                                            <div class="col-4">

                                                {{ $item->qty }}

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>
    </div>

</div>

@endsection