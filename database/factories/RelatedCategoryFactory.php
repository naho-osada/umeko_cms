<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RelatedCategory;

class RelatedCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RelatedCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $article_id = mt_rand(4, 15);
        $category_id = mt_rand(4, 20);
        return [
            'article_id' => $article_id,
            'category_id' => $category_id,
        ];
    }
}
