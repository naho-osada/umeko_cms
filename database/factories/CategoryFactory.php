<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->word(10);
        $user_id = mt_rand(1, 5);
        return [
            'category_name' => $name,
            'disp_name' => $name,
            'user_id' => $user_id,
            'updated_user_id' => $user_id,
        ];
    }
}
