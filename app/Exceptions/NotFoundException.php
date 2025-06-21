<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class NotFoundException extends \RuntimeException
{
    public function __construct(protected string $model)
    {
        parent::__construct($model);
    }

    public function render():JsonResponse
    {
        return response()->json([
            'error'=>'not found '.$this->model
        ],JsonResponse::HTTP_NOT_FOUND);
    }
}
