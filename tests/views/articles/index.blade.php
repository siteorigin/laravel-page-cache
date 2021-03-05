<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Test Home Page</title>
</head>
<body>
@foreach($articles as $article)
    <p><a href="{{ route('articles.show', $article) }}">{{ $article->title }}</a></p>
@endforeach

{{ $articles->render() }}
</body>
</html>