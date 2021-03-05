<?php

namespace SiteOrigin\PageCache\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SiteOrigin\PageCache\Tests\App\Article;

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
        return [
            'title' => $this->faker->text(80),
            'content' => '<p>' . implode("</p>\n\n<p>", $this->faker->paragraphs(10)) . '</p>',
        ];
    }

    public function configure()
    {
        return $this;
    }
}
