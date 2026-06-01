<?php

namespace Modules\Core\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Modules\Core\Models\Category;
use Modules\Core\Models\Product;
use Modules\Core\Models\ProductVariant;

class ProductImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    private int $storeId;
    public array $errors = [];
    public int $imported = 0;

    public function __construct(int $storeId)
    {
        $this->storeId = $storeId;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2; // row number in spreadsheet (1-indexed + heading row)

            $name = trim($row['name'] ?? '');
            if (!$name) {
                $this->errors[] = "Baris {$line}: kolom 'name' wajib diisi.";
                continue;
            }

            $stockType = strtolower(trim($row['stock_type'] ?? 'normal'));
            if (!in_array($stockType, ['normal', 'serial', 'bulk'])) {
                $this->errors[] = "Baris {$line}: stock_type '{$stockType}' tidak valid (normal/serial/bulk).";
                continue;
            }

            $price = floatval($row['price'] ?? 0);
            if ($price < 0) {
                $this->errors[] = "Baris {$line}: price tidak boleh negatif.";
                continue;
            }

            // Resolve category by name
            $categoryId = null;
            $categoryName = trim($row['category'] ?? '');
            if ($categoryName) {
                $category = Category::where('store_id', $this->storeId)
                    ->whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
                    ->first();
                if (!$category) {
                    $category = Category::create([
                        'store_id' => $this->storeId,
                        'name'     => $categoryName,
                    ]);
                }
                $categoryId = $category->id;
            }

            $unitType = strtolower(trim($row['unit_type'] ?? 'pcs'));
            if (!in_array($unitType, ['pcs', 'weight'])) {
                $unitType = 'pcs';
            }

            $product = Product::create([
                'store_id'    => $this->storeId,
                'category_id' => $categoryId,
                'name'        => $name,
                'barcode'     => trim($row['barcode'] ?? '') ?: null,
                'description' => trim($row['description'] ?? '') ?: null,
                'stock_type'  => $stockType,
                'has_variants' => false,
                'is_active'   => true,
            ]);

            $sku = trim($row['sku'] ?? '');
            if (!$sku) {
                $sku = strtoupper(Str::slug($product->name, '-')) . '-' . $product->id;
            }

            ProductVariant::create([
                'product_id' => $product->id,
                'name'       => $name,
                'sku'        => $sku,
                'barcode'    => trim($row['barcode'] ?? '') ?: null,
                'price'      => $price,
                'cost'       => floatval($row['cost'] ?? 0),
                'unit'       => trim($row['unit'] ?? 'pcs') ?: 'pcs',
                'unit_type'  => $unitType,
                'is_active'  => true,
            ]);

            $this->imported++;
        }
    }
}
