<?php

namespace App\Services;

use App\Enums\ProductItemStatus;
use App\Models\Product;
use App\Models\ProductItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FileUploadService
{
    private const PARSER_METHODS = [
        'csv' => 'parseCsv',
        'txt' => 'parseCsv',
        'json' => 'parseJson',
        'xlsx' => 'parseExcel',
        'xls' => 'parseExcel',
    ];

    public function processFile(UploadedFile $file, Product $product): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            $items = $this->parseFileByExtension($file, $extension);

            if (empty($items)) {
                return $this->errorResponse('No valid items found in the file.');
            }

            $count = $this->createProductItems($product, $items);

            return $this->successResponse($count);
        } catch (\Exception $e) {
            return $this->errorResponse('Error processing file: ' . $e->getMessage());
        }
    }

    private function parseFileByExtension(UploadedFile $file, string $extension): array
    {
        if (!isset(self::PARSER_METHODS[$extension])) {
            throw new \Exception("Unsupported file format: {$extension}");
        }

        $parserMethod = self::PARSER_METHODS[$extension];

        return $this->$parserMethod($file);
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

        $this->validateJsonData($data);

        return $this->extractItemsFromJsonData($data);
    }

    private function validateJsonData($data): void
    {
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON format: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new \Exception('JSON must contain an array of items.');
        }
    }

    private function extractItemsFromJsonData(array $data): array
    {
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
            $rowData = $this->extractRowData($row);

            if (!empty($rowData)) {
                $items[] = implode(':', $rowData);
            }
        }

        return $items;
    }

    private function extractRowData($row): array
    {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $rowData = [];

        foreach ($cellIterator as $cell) {
            $value = $cell->getValue();
            if ($value !== null && $value !== '') {
                $rowData[] = trim((string) $value);
            }
        }

        return $rowData;
    }

    private function createProductItems(Product $product, array $items): int
    {
        $count = 0;
        $batchSize = config('shop.file_upload.batch_size');
        $batch = [];

        DB::beginTransaction();

        try {
            foreach ($items as $content) {
                $batch[] = $this->buildProductItemData($product->id, $content);

                if (count($batch) >= $batchSize) {
                    $count += $this->insertBatch($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                $count += $this->insertBatch($batch);
            }

            DB::commit();

            return $count;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function buildProductItemData(int $productId, string $content): array
    {
        return [
            'product_id' => $productId,
            'content' => $content,
            'status' => ProductItemStatus::AVAILABLE->value,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function insertBatch(array $batch): int
    {
        ProductItem::insert($batch);
        return count($batch);
    }

    private function successResponse(int $count): array
    {
        return [
            'success' => true,
            'message' => "Successfully imported {$count} items.",
            'count' => $count,
        ];
    }

    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
            'count' => 0,
        ];
    }
}
