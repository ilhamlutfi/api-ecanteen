<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseResource extends JsonResource
{
    public $status, $message, $metadata;
    protected $statusCode;

    // $status, $message, $data, $metadata
    public function __construct($status, $message, $data, $metadata = [], $statusCode = 200)
    {
        parent::__construct(null);

        $this->resource = [
            'status'    => $status,
            'message'   => $message,
            'data'      => $data,
            'metadata'  => $metadata
        ];

        $this->statusCode = $statusCode;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }

    public function toResponse($request)
    {
        return response()->json($this->resource, $this->statusCode);
    }
}
