<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Article;

class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->realText(30);
        $str = $this->faker->realText(500);
        $description = $this->faker->realText(100);
        $status= mt_rand(1, 3);
        $auth= mt_rand(1, 2);
        $path = $this->faker->unique->randomNumber(5);
        $date = $this->faker->date($format='Y-m-d',$max='-1 year');
        $update = $this->faker->date($format='Y-m-d',$max=$date, $min='-2 year');
        $user_id = mt_rand(1, 5);
        return [
            'title' => $title,
            'contents' => $str,
            'status' => $status,
            'path' => $path,
            'article_auth' => $auth,
            'seo_description' => $description,
            'user_id' => $user_id,
            'created_at' => $date,
            'updated_user_id' => $user_id,
            'updated_at' => $update,
        ];
    }
}
