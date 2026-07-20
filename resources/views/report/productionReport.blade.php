@extends('layouts.master')

@section('content')

<div class="container">

    <h4 class="mb-3">
        Laporan Produksi
    </h4>

    <div class="card mb-3">

        <div class="card-body">

            <form method="GET">

                <div class="row">

                    <div class="col-md-4">

                        <label>
                            Mulai Tanggal
                        </label>

                        <input
                            type="date"
                            name="start_date"
                            class="form-control"
                            value="{{ request('start_date') }}"
                        >

                    </div>

                    <div class="col-md-4">

                        <label>
                            Akhir Tanggal
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

            </form>

        </div>

    </div>

    <div class="table-responsive">

        <table class="table table-bordered">

            <thead>

                <tr>

                    <th>
                        Product
                    </th>

                    <th>
                        Size
                    </th>

                    <th>
                        Planning Target
                    </th>

                    <th>
                        Stage 1 Output
                    </th>

                    <th>
                        Stage 2 Output
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($report as $item)

                    <tr>

                        <td>
                            {{ $item['product'] }}
                        </td>

                        <td>
                            {{ $item['size'] }}
                        </td>

                        <td>
                            {{ $item['planning_target'] }}
                        </td>

                        <td>
                            {{ $item['stage1_output'] }}
                        </td>

                        <td>
                            {{ $item['stage2_output'] }}
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="text-center"
                        >

                            No Data

                        </td>

                    </tr>

                @endforelse

            </tbody>

            <tfoot>

                <tr>

                    <th colspan="2">

                        Total

                    </th>

                    <th>

                        {{ $report->sum('planning_target') }}

                    </th>

                    <th>

                        {{ $report->sum('stage1_output') }}

                    </th>

                    <th>

                        {{ $report->sum('stage2_output') }}

                    </th>

                </tr>

            </tfoot>

        </table>

    </div>

</div>

@endsection