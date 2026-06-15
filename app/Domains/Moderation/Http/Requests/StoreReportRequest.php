<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Http\Requests;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'target_type' => ['required', 'string', Rule::in(['post', 'comment', 'community', 'user', 'ad', 'message'])],
            'target_id' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $class = Relation::getMorphedModel($this->input('target_type'));

            if ($class === null || ! class_exists($class)) {
                $validator->errors()->add('target_type', 'Недопустимый тип объекта жалобы.');

                return;
            }

            if (! $class::query()->whereKey($this->input('target_id'))->exists()) {
                $validator->errors()->add('target_id', 'Объект жалобы не найден.');
            }
        });
    }
}
