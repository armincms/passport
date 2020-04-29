<?php

namespace Component\LaravelPassport\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LaravelPassportCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
