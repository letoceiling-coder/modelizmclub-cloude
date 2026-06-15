<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Catalog\Enums\CategoryType;
use App\Domains\Catalog\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Дерево справочников: content (лента/интересы), community (сообщества), ad (объявления).
     * Значения вложенности — пример отраслевой структуры стендового моделизма.
     */
    public function run(): void
    {
        $tree = [
            CategoryType::Content->value => [
                'Сборные модели' => ['Авиация', 'Бронетехника', 'Флот', 'Автомобили', 'Фигурки'],
                'Инструменты и материалы' => ['Краски', 'Клеи', 'Аэрографы', 'Шлифовка'],
                'Техники' => ['Окраска', 'Везеринг', 'Диорамы', 'Конверсии'],
                'Новости и события' => ['Выставки', 'Конкурсы', 'Новинки'],
            ],
            CategoryType::Community->value => [
                'Авиамоделизм' => [],
                'Бронетехника' => [],
                'Флот' => [],
                'Автомоделизм' => [],
                'Диорамы и виньетки' => [],
                'Фигурки и фэнтези' => [],
            ],
            CategoryType::Ad->value => [
                'Наборы моделей' => ['Авиация', 'Бронетехника', 'Флот', 'Автомобили'],
                'Инструменты' => ['Аэрографы', 'Компрессоры', 'Резаки'],
                'Расходники' => ['Краски', 'Клеи', 'Декали'],
                'Литература' => ['Книги', 'Журналы', 'Чертежи'],
            ],
        ];

        foreach ($tree as $type => $roots) {
            $rootPosition = 0;
            foreach ($roots as $rootName => $children) {
                $root = $this->makeCategory($type, $rootName, $rootPosition++);

                $childPosition = 0;
                foreach ($children as $childName) {
                    $child = $this->makeCategory($type, $childName, $childPosition++, $root->id);
                    $child->appendToNode($root)->save();
                }
            }
        }
    }

    private function makeCategory(string $type, string $name, int $position, ?int $parentId = null): Category
    {
        return Category::query()->firstOrCreate(
            [
                'type' => $type,
                'slug' => $this->slug($name).'-'.$type,
            ],
            [
                'name' => $name,
                'position' => $position,
                'is_active' => true,
            ],
        );
    }

    private function slug(string $name): string
    {
        $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '',
            'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ];

        $latin = strtr(Str::lower($name), $map);

        return Str::slug($latin);
    }
}
