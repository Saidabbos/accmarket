<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FileUploadService
{
    public function processFile(UploadedFile $file, Product $product): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            $items = match ($extension) {
                'csv', 'txt' => $this->parseCsv($file),
                'json' => $this->parseJson($file),
                'xlsx', 'xls' => $this->parseExcel($file),
                default => throw new \Exception("Unsupported file format: {$extension}"),
            };

            if (empty($items)) {
                return [
                    'success' => false,
                    'message' => 'No valid items found in the file.',
                    'count' => 0,
                ];
            }

            $count = $this->createProductItems($product, $items);

            return [
                'success' => true,
                'message' => "Successfully imported {$count} items.",
                'count' => $count,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error processing file: ' . $e->getMessage(),
                'count' => 0,
            ];
        }
    }

    private function parseCsv(UploadedFile $file): array
    {
        $items = [];
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw new \Exception('Could not open file for reading.');
        }

        while (($line = fgets($handle)) !== false) {
            $content = trim($line);
            if (!empty($content)) {
                $items[] = $content;
            }
        }

        fclose($handle);

        return $items;
    }

    private function parseJson(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON format: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new \Exception('JSON must contain an array of items.');
        }

        $items = [];
        foreach ($data as $item) {
            if (is_string($item)) {
                $items[] = trim($item);
            } elseif (is_array($item) && isset($item['content'])) {
                $items[] = trim($item['content']);
            } elseif (is_array($item) && isset($item['data'])) {
                $items[] = trim($item['data']);
            } elseif (is_array($item)) {
                $items[] = trim(implode(':', array_values($item)));
            }
        }

        return array_filter($items);
    }

    private function parseExcel(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $items = [];

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();
                if ($value !== null && $value !== '') {
                    $rowData[] = trim((string) $value);
                }
            }

            if (!empty($rowData)) {
                $items[] = implode(':', $rowData);
            }
        }

        return $items;
    }

    private function createProductItems(Product $product, array $items): int
    {
        $count = 0;
        $batchSize = 500;
        $batch = [];

        DB::beginTransaction();

        try {
            foreach ($items as $content) {
                $batch[] = [
                    'product_id' => $product->id,
                    'content' => $content,
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= $batchSize) {
                    ProductItem::insert($batch);
                    $count += count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                ProductItem::insert($batch);
                $count += count($batch);
            }

            DB::commit();

            return $count;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
