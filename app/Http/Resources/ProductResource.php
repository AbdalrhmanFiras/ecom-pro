<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,
            'warranty' => $this->when(!is_null($this->warranty), $this->warranty),
            'description' => $this->when(!is_null($this->description), $this->description),
            // that mean the dec is not null the fun will return true and the fun when will work ,

            // $this->when(isset($this->description), $this->description),


        ];
    }
}
