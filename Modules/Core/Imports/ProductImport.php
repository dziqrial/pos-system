<?php

namespace Modules\Core\Imports;

use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Reader\ODS\Reader as OdsReader;
use Illuminate\Support\Str;
use Modules\Core\Models\Category;
use Modules\Core\Models\Product;
use Modules\Core\Models\ProductVariant;

class ProductImport
{
    public array $errors   = [];
    public int   $imported = 0;

    public function __construct(private int $storeId) {}

    public function import(string $filePath, string $extension): void
    {
        $reader = match (strtolower($extension)) {
            'csv'        => new CsvReader(),
            'ods'        => new OdsReader(),
            default      => new XlsxReader(),  // xlsx, xls
        };

        $reader->open($filePath);

        $headers = [];
        $rowIndex = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = array_map(
                    fn ($cell) => trim((string) $cell->getValue()),
                    $row->getCells()
                );

                // First row = headers
                if ($rowIndex === 0) {
                    $headers = array_map('strtolower', $cells);
                    $rowIndex++;
                    continue;
                }

                $rowIndex++;

                if (empty(array_filter($cells))) {
                    continue; // skip empty rows
                }

                $data = array_combine($headers, array_pad($cells, count($headers), ''));

                $this->processRow($data, $rowIndex);
            }
            break; // only first sheet
        }

        $reader->close();
    }

    private function processRow(array $data, int $line): void
    {
        $name = $data['name'] ?? '';
        if (!$name) {
            $this->errors[] = "Baris {$line}: kolom 'name' wajib diisi.";
            return;
        }

        $stockType = strtolower($data['stock_type'] ?? 'normal');
        if (!in_array($stockType, ['normal', 'serial', 'bulk'])) {
            $stockType = 'normal';
        }

        $price = floatval(str_replace([',', ' '], '', $data['price'] ?? 0));
        if ($price < 0) {
            $this->errors[] = "Baris {$line}: price tidak boleh negatif.";
            return;
        }

        $categoryId   = $this->resolveCategory($data['category'] ?? '');
        $unitType     = in_array($data['unit_type'] ?? 'pcs', ['pcs', 'weight']) ? $data['unit_type'] : 'pcs';

        $product = Product::create([
            'store_id'    => $this->storeId,
            'category_id' => $categoryId,
            'name'        => $name,
            'barcode'     => $data['barcode'] ?: null,
            'description' => $data['description'] ?: null,
            'stock_type'  => $stockType,
            'has_variants' => false,
            'is_active'   => true,
        ]);

        $sku = $data['sku'] ?: strtoupper(Str::slug($product->name, '-')) . '-' . $product->id;

        ProductVariant::create([
            'product_id' => $product->id,
            'name'       => $name,
            'sku'        => $sku,
            'barcode'    => $data['barcode'] ?: null,
            'price'      => $price,
            'cost'       => floatval(str_replace([',', ' '], '', $data['cost'] ?? 0)),
            'unit'       => $data['unit'] ?: 'pcs',
            'unit_type'  => $unitType,
            'is_active'  => true,
        ]);

        $this->imported++;
    }

    private function resolveCategory(string $name): ?int
    {
        if (!$name) return null;

        $category = Category::where('store_id', $this->storeId)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if (!$category) {
            $category = Category::create([
                'store_id' => $this->storeId,
                'name'     => $name,
            ]);
        }

        return $category->id;
    }
}
