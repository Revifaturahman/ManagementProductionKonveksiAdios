<?php
namespace App\Services;

use App\Models\ProductionBatchDetail;
use App\Models\ProductionBatchDetailProcess;
use App\Models\ProductVariant;
use App\Models\SemiProduct;

class ProductionService
{
    public function getStagesByCategory($categoryName)
    {
        if ($categoryName == 'oblong') {
            return ['obras', 'tailor', 'obras', 'overdeck_bawah'];
        }

        if ($categoryName == 'berkerah') {
            return ['tailor', 'obras', 'tailor', 'overdeck_bawah'];
        }

        return [];
    }

    public function handleProductionItem($batchId, $variantId, $qty, $workerId = null)
    {
        // 🥇 CEK SEMI
        $semi = SemiProduct::where('product_variant_id', $variantId)->first();

        if (!$semi || $semi->stock < $qty) {
            throw new \Exception("Stok tidak cukup");
        }

        // 🥈 KURANGI SEMI
        $semi->decrement('stock', $qty);

        // 🥉 CREATE ITEM
        $item = ProductionBatchDetail::create([
            'production_batch_id' => $batchId,
            'product_variant_id'  => $variantId,
            'worker_id'           => $workerId,
            'qty'                 => $qty,
        ]);

        // 🧠 AMBIL CATEGORY
        $variant = ProductVariant::with('product.category')->find($variantId);
        $category = $variant->product->category->name;

        // 🔥 AMBIL STAGE
        $stages = $this->getStagesByCategory($category);

        // 🔁 GENERATE PROCESS
        foreach ($stages as $stage) {
            ProductionBatchDetailProcess::create([
                'production_batch_detail_id' => $item->id,
                'stage' => $stage,
                'qty' => $qty,
                'status' => 'pending'
            ]);
        }
    }
}