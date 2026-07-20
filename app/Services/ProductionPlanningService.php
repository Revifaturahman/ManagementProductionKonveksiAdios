<?php
namespace App\Services;

use App\Models\ProductionPlanning;
use App\Models\ProductionPlanningItem;
use App\Models\ProductVariant;
use App\Models\RawMaterialMaster;

class ProductionPlanningService
{
    private function getVariantStocks()
    {
        return ProductVariant::with([
            'semiProduct',
            'finishedProduct',
            'product'
        ])
        ->get()
        ->map(function ($variant) {

            $semiQty =
                $variant->semiProduct?->qty ?? 0;

            $finishedQty =
                $variant->finishedProduct?->qty ?? 0;

            $totalStock =
                $semiQty + $finishedQty;

            return [

                'variant' => $variant,

                'total_stock' => $totalStock,

                'minimum_stock' =>
                    $variant->minimum_stock,

                'target_stock' =>
                    $variant->minimum_stock * 2,

                // untuk prioritas
                'priority_gap' =>
                    $variant->minimum_stock
                    - $totalStock,

                // untuk kebutuhan produksi
                'production_gap' =>
                    ($variant->minimum_stock * 2)
                    - $totalStock
            ];
        });
    }
    private function sortByPriority(
        $variants
    )
    {
        return $variants
            ->sortByDesc(
                'priority_gap'
            )
            ->values();
    }

    private function calculateRequiredKg(
        $variants
    )
    {
        return collect($variants)
            ->map(function ($item) {

                $needQty = max(
                    0,
                    $item['production_gap']
                );

                $ratioPerKg =
                    $item['variant']
                        ->product
                        ->ratio_per_kg;

                $requiredKg = ceil(
                    $needQty / $ratioPerKg
                );

                $item['required_qty']
                    = $needQty;

                $item['required_kg']
                    = $requiredKg;

                return $item;
            });
    }
    private function calculateRemainingKg(
        $availableKg,
        $variants
    )
    {
        $requiredKg =
            collect($variants)
                ->sum('required_kg');

        return max(
            0,
            $availableKg - $requiredKg
        );
    }

    private function allocateRatioKg(
        $variants,
        $remainingKg
    )
    {
        $totalRatio =
            collect($variants)
                ->sum(function ($item) {

                    return $item['variant']
                        ->product
                        ->allocation_ratio;
                });

        return collect($variants)
            ->map(function ($item)
            use ($remainingKg, $totalRatio) {

                $ratio =
                    $item['variant']
                        ->product
                        ->allocation_ratio;

                $extraKg = floor(
                    ($ratio / $totalRatio)
                    * $remainingKg
                );

                $item['allocated_kg']
                    = $extraKg;

                return $item;
            });
    }

    private function generateSuggestions(
        $variants
    )
    {
        $priority = 1;

        return collect($variants)
            ->map(function ($item)
            use (&$priority) {

                $variant =
                    $item['variant'];

                $finalKg =
                    $item['required_kg']
                    +
                    $item['allocated_kg'];

                $estimatedQty =
                    $finalKg *
                    $variant->product->ratio_per_kg;

                return [

                    'product_variant_id'
                        => $variant->id,

                    'product_name'
                        => $variant->product->name,

                    'size'
                        => $variant->size,

                    'priority_order'
                        => $priority++,

                    'suggested_kg'
                        => $finalKg,

                    'suggested_qty'
                        => $estimatedQty,

                    'ratio_per_kg'
                        => $variant->product->ratio_per_kg,
                ];
            });
    }

    public function generateSuggestionsFromRawMaterial(
        RawMaterialMaster $rawMaterial
    )
    {
        $variants =
            $this->getVariantStocks();

        $variants =
            $this->sortByPriority(
                $variants
            );

        $variants =
            $this->calculateRequiredKg(
                $variants
            );

        $availableKg = max(
            0,
            $rawMaterial
                ->stock
                ->stock_kg - 50
        );

        $remainingKg =
            $this->calculateRemainingKg(
                $availableKg,
                $variants
            );

        $variants =
            $this->allocateRatioKg(
                $variants,
                $remainingKg
            );

        return $this->generateSuggestions(
            $variants
        );
    }
}