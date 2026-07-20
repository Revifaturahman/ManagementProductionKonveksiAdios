<?php
namespace App\Services;

use App\Models\ProductionBatchDetail;
use App\Models\ProductVariant;

class ProductionBatchService
{
    private function determinePriorityType(): array
    {
        $variants = ProductVariant::with([
            'product.category',
            'finishedProduct'
        ])->get();

        $totals = [

            'oblong' => 0,

            'berkerah' => 0,
        ];

        foreach ($variants as $variant) {

            $finishedQty =
                $variant->finishedProduct?->qty ?? 0;

            $qtyInProcess =
                $this->getQtyInProcess(
                    $variant->id
                );

            $effectiveStock =
                $finishedQty
                +
                $qtyInProcess;

            $shortage = max(
                0,
                $variant->minimum_stock
                - $effectiveStock
            );

            $categoryName = strtolower(
                $variant
                    ->product
                    ->category
                    ->name
                );

                $type = str_contains(
                $categoryName,
                'oblong'
            )
                ? 'oblong'
                : 'berkerah';

            $totals[$type] += $shortage;
        }

        $priorityType =
            $totals['oblong']
            >=
            $totals['berkerah']
            ? 'oblong'
            : 'berkerah';

        return [

            'type' => $priorityType,

            'total_shortage' =>
                $totals[$priorityType],

            'oblong_shortage' =>
                $totals['oblong'],

            'berkerah_shortage' =>
                $totals['berkerah'],
        ];
    }

    private function getVariantStocks(
        string $type
    )
    {
        return ProductVariant::with([
            'semiProduct',
            'finishedProduct',
            'product.category'
        ])

        ->whereHas(
            'product.category',
            function ($query)
            use ($type) {

                $query->where(
                    'name',
                    'like',
                    '%' . $type . '%'
                );
            }
        )

        ->get()

        ->map(function ($variant) {

            $semiQty =
                $variant->semiProduct?->qty ?? 0;

            $finishedQty =
                $variant->finishedProduct?->qty ?? 0;

            $qtyInProcess =
                $this->getQtyInProcess(
                    $variant->id
                );

            $effectiveStock =
                $finishedQty
                +
                $qtyInProcess;

            $shortageQty = max(
                0,
                $variant->minimum_stock
                - $effectiveStock
            );

            return [

                'variant' => $variant,

                'semi_qty' =>
                    $semiQty,

                'finished_qty' =>
                    $finishedQty,

                'qty_in_process' =>
                    $qtyInProcess,

                'effective_stock' =>
                    $effectiveStock,

                'minimum_stock' =>
                    $variant->minimum_stock,

                'shortage_qty' =>
                    $shortageQty,
            ];
        })

        ->filter(function ($item) {

            return
                $item['shortage_qty'] > 0
                &&
                $item['semi_qty'] > 0;
        })

        ->values();
    }

    private function sortByPriority(
        $variants
    )
    {
        return collect($variants)

            ->sortByDesc(
                'shortage_qty'
            )

            ->values();
    }

    private function allocateTaskQty(
        $variants,
        int $maxQty = 120
    )
    {
        $remainingCapacity =
            $maxQty;

        return collect($variants)

            ->map(function ($item)
            use (&$remainingCapacity) {

                if (
                    $remainingCapacity <= 0
                ) {

                    $item['take_qty']
                        = 0;

                    return $item;
                }

                $takeQty = min(

                    $item['shortage_qty'],

                    $item['semi_qty'],

                    $remainingCapacity
                );

                $item['take_qty']
                    = $takeQty;

                $remainingCapacity
                    -= $takeQty;

                return $item;
            })

            ->filter(function ($item) {

                return
                    $item['take_qty'] > 0;
            })

            ->values();
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

                return [

                    'product_variant_id'
                        => $variant->id,

                    'product_name'
                        => $variant
                            ->product
                            ->name,

                    'size'
                        => $variant
                            ->size,

                    'priority_order'
                        => $priority++,

                    'semi_qty'
                        => $item['semi_qty'],

                    'finished_qty'
                        => $item['finished_qty'],

                    'minimum_stock'
                        => $item['minimum_stock'],

                    'shortage_qty'
                        => $item['shortage_qty'],

                    'take_qty'
                        => $item['take_qty'],
                ];
            })

            ->values();
    }

    public function generateStageTwoSuggestions()
    {
        $typeData =
            $this->determinePriorityType();

        $variants =
            $this->getVariantStocks(
                $typeData['type']
            );

        $variants =
            $this->sortByPriority(
                $variants
            );

        $variants =
            $this->allocateTaskQty(
                $variants
            );

        return [

            'type' =>
                $typeData['type'],

            'total_shortage' =>
                $typeData['total_shortage'],

            'oblong_shortage' =>
                $typeData['oblong_shortage'],

            'berkerah_shortage' =>
                $typeData['berkerah_shortage'],

            'items' =>
                $this->generateSuggestions(
                    $variants
                ),
        ];
    }
    private function getQtyInProcess(
        int $variantId
    ): int
    {
        return ProductionBatchDetail::query()

            ->where(
                'product_variant_id',
                $variantId
            )

            ->whereHas(
                'batch',
                function ($query) {

                    $query->whereIn(
                        'status',
                        [
                            'pending',
                            'process'
                        ]
                    );
                }
            )

            ->sum('qty');
    }
}
