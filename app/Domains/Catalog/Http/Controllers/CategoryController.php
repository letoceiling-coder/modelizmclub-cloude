<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Http\Controllers;

use App\Domains\Catalog\Enums\CategoryType;
use App\Domains\Catalog\Http\Requests\StoreCategoryRequest;
use App\Domains\Catalog\Http\Requests\UpdateCategoryRequest;
use App\Domains\Catalog\Http\Resources\CategoryResource;
use App\Domains\Catalog\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /** Дерево категорий конкретного справочника (content|community|ad). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $type = $request->string('type')->toString() ?: CategoryType::Content->value;
        abort_unless(in_array($type, CategoryType::values(), true), 422, 'Неизвестный тип справочника.');

        $query = Category::ofType($type);

        if (! $request->user()?->can('categories.manage')) {
            $query->active();
        }

        $tree = $query->defaultOrder()->get()->toTree();

        return CategoryResource::collection($tree);
    }

    public function show(Category $category): CategoryResource
    {
        return CategoryResource::make($category->load('children'));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorizeManage();

        $category = new Category($request->validated());
        $category->save();

        if ($parentId = $request->input('parent_id')) {
            $category->appendToNode(Category::findOrFail($parentId))->save();
        }

        return CategoryResource::make($category)->response()->setStatusCode(201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $this->authorizeManage();

        $category->fill($request->safe()->except('parent_id'));
        $category->save();

        if ($request->has('parent_id')) {
            $parentId = $request->input('parent_id');
            if ($parentId === null) {
                $category->saveAsRoot();
            } else {
                $category->appendToNode(Category::findOrFail($parentId))->save();
            }
        }

        return CategoryResource::make($category->fresh());
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorizeManage();
        $category->delete();

        return response()->json(['message' => 'Категория удалена.']);
    }

    /** Drag-and-drop изменение порядка/родителя внутри справочника. */
    public function reorder(Request $request): JsonResponse
    {
        $this->authorizeManage();

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:categories,id'],
            'items.*.parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'items.*.position' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($data['items'] as $item) {
            $category = Category::findOrFail($item['id']);
            $category->position = $item['position'];

            if (($item['parent_id'] ?? null) === null) {
                $category->saveAsRoot();
            } else {
                $category->appendToNode(Category::findOrFail($item['parent_id']))->save();
            }
        }

        Category::fixTree();

        return response()->json(['message' => 'Порядок категорий обновлён.']);
    }

    private function authorizeManage(): void
    {
        abort_unless(
            request()->user()?->can('categories.manage') ?? false,
            403,
            'Недостаточно прав для управления категориями.',
        );
    }
}
