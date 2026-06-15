<?php

declare(strict_types=1);

namespace App\Support\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collection' => $this->collection_name,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'url' => $this->getFullUrl(),
            'thumb' => $this->hasGeneratedConversion('thumb') ? $this->getFullUrl('thumb') : null,
            'medium' => $this->hasGeneratedConversion('medium') ? $this->getFullUrl('medium') : null,
            'order' => $this->order_column,
        ];
    }
}
