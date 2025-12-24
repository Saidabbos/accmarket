<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseAction
{
    /**
     * Execute the action with database transaction handling
     *
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function execute(array $data = [])
    {
        try {
            DB::beginTransaction();

            $result = $this->handle($data);

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error(static::class . ' failed: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle the action logic
     * This method must be implemented by child classes
     *
     * @param array $data
     * @return mixed
     */
    abstract protected function handle(array $data);
}
