<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SaveFile;

class SaveFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SaveFile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'year' => date('Y'),
            'month' => date('m'),
            'type' => 1,
            'filename' => $this->faker->word() . '.jpg',
            'description' => '',
            'user_id' => mt_rand(1, 5),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
