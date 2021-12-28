<?php

namespace Database\Factories;

use App\Models\Users;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UsersFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Users::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->name;
        return [
            'user_name' => $name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('test'),
            'auth' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
