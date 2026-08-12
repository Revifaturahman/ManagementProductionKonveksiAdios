<?php

namespace App\Http\Controllers;

use App\Models\ProductionPeriod;
use Illuminate\Http\Request;

class ProductionPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periods = ProductionPeriod::latest()->paginate(10);

        return view(
            'production_period.index',
            compact('periods')
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => [
                'required',
                'date'
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date'
            ]
        ]);

        /*
        |--------------------------------------------------------------------------
        | CHECK OVERLAPPING PERIOD
        |--------------------------------------------------------------------------
        */

        $exists = ProductionPeriod::where(function ($query) use ($request) {

            $query->where('start_date', '<=', $request->end_date)
                ->where('end_date', '>=', $request->start_date);

        })->exists();

        if ($exists) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Periode gagal ditambahkan karena tanggal yang dipilih bertumpang tindih dengan periode produksi yang sudah ada.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | GENERATE CODE
        |--------------------------------------------------------------------------
        */

        $last = ProductionPeriod::latest('id')->first();

        $number = $last
            ? ((int) substr($last->code, 3)) + 1
            : 1;

        /*
        |--------------------------------------------------------------------------
        | CREATE PERIOD
        |--------------------------------------------------------------------------
        */

        ProductionPeriod::create([

            'code' => 'PRD' . str_pad(
                $number,
                3,
                '0',
                STR_PAD_LEFT
            ),

            'start_date' => $request->start_date,

            'end_date' => $request->end_date,

            'status' => 'pending',

        ]);

        return back()->with(
            'success',
            'Periode berhasil ditambahkan.'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        ProductionPeriod $productionPeriod
    )
    {
        $request->validate([
            'start_date' => [
                'required',
                'date'
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date'
            ]
        ]);

        /*
        |--------------------------------------------------------------------------
        | ACTIVE PERIOD CANNOT BE EDITED
        |--------------------------------------------------------------------------
        */

        if ($productionPeriod->status == 'active') {

            return back()->with(
                'error',
                'Periode produksi gagal diubah karena masih berstatus aktif.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CHECK OVERLAPPING PERIOD
        |--------------------------------------------------------------------------
        */

        $exists = ProductionPeriod::where('id', '!=', $productionPeriod->id)
            ->where(function ($query) use ($request) {

                $query->where('start_date', '<=', $request->end_date)
                    ->where('end_date', '>=', $request->start_date);

            })->exists();

        if ($exists) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Periode produksi gagal diubah karena tanggal yang dipilih bertumpang tindih dengan periode produksi yang sudah ada.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE PERIOD
        |--------------------------------------------------------------------------
        */

        $productionPeriod->update([

            'start_date' => $request->start_date,

            'end_date' => $request->end_date,

            'status' => $productionPeriod->status

        ]);

        return back()->with(
            'success',
            'Periode berhasil diubah.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        ProductionPeriod $productionPeriod
    )
    {
        if ($productionPeriod->status == 'active') {

            return back()->with(
                'error',
                'Periode produksi gagal dihapus karena masih berstatus aktif.'
            );
        }

        if (
            $productionPeriod
                ->plannings()
                ->exists()
        ) {
            return back()->with(
                'error',
                'Periode sudah digunakan pada planning.'
            );
        }

        $productionPeriod->delete();

        return back()->with(
            'success',
            'Periode berhasil dihapus.'
        );
    }
}
