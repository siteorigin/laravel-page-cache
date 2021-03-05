<?php

namespace SiteOrigin\PageCache\Tests\App;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class ArticleController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function show(Article $article)
    {
        dd($article);
    }
}