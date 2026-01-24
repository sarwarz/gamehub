<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'slug'          => $this->slug,
            'sku'           => $this->sku,
            'description'   => $this->description,
            'image'         => $this->primaryImage?->url,
            'gallery'       => $this->galleryImages->map(fn ($m) => $m->url),
            'extra_attributes'    => $this->attributes,
            'system_requirements' => $this->system_requirements,
            'delivery_type' => $this->delivery_type,
            'status'        => $this->status,
            'is_featured'   => $this->is_featured,
            'sort_order'    => $this->sort_order,

            'seo' => [
                'meta_title'       => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords'    => $this->meta_keywords,
            ],

            'product_attributes' => [
                'categories' => $this->categories,
                'platforms'  => $this->platforms,
                'types'      => $this->types,
                'regions'    => $this->regions,
                'languages'  => $this->languages,
                'works_on'   => $this->worksOn,
                'developer'  => $this->developer,
                'publisher'  => $this->publisher,
                'label'      => $this->label,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
