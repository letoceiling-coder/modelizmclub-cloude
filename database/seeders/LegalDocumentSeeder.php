<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Support\Enums\LegalDocumentType;
use App\Domains\Support\Models\LegalDocument;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (LegalDocumentType::cases() as $type) {
            LegalDocument::query()->updateOrCreate(
                ['type' => $type->value, 'version' => '1.0'],
                [
                    'title' => $type->label(),
                    'content' => '<p>Текст документа «'.$type->label().'» будет опубликован администрацией.</p>',
                    'is_current' => true,
                    'published_at' => now(),
                ],
            );
        }
    }
}
