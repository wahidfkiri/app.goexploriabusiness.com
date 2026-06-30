<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->seo_title ?: $post->title }} - {{ $etablissement->name }}</title>
    <meta name="description" content="{{ $post->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?: $post->content), 155) }}">
    <meta name="keywords" content="{{ $post->seo_keywords }}">
    @if($post->canonical_url)
        <link rel="canonical" href="{{ $post->canonical_url }}">
    @endif

    <meta property="og:title" content="{{ $post->seo_title ?: $post->title }}">
    <meta property="og:description" content="{{ $post->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?: $post->content), 155) }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($post->og_image_url ?: $post->featured_image)
        <meta property="og:image" content="{{ $post->og_image_url ?: $post->featured_image }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #f3f6fb;
            --ink: #16273f;
            --muted: #617086;
            --line: #d6deea;
            --card: #ffffff;
            --primary: #0b6ef5;
            --soft: #e9f1ff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            background:
                radial-gradient(circle at 80% 0%, #d6ebff, transparent 35%),
                radial-gradient(circle at 0% 20%, #e5efff, transparent 30%),
                var(--bg);
            color: var(--ink);
        }
        .wrap {
            max-width: 980px;
            margin: 0 auto;
            padding: 22px;
        }
        .back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            margin-bottom: .9rem;
        }
        .hero {
            border: 1px solid var(--line);
            border-radius: 22px;
            background: linear-gradient(140deg, #ffffff, #eef5ff 70%);
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .hero h1 {
            margin: .5rem 0 .4rem;
            line-height: 1.25;
            font-size: clamp(1.5rem, 3vw, 2.2rem);
        }
        .meta {
            color: #708198;
            font-size: .88rem;
        }
        .hero-img {
            width: 100%;
            max-height: 430px;
            object-fit: cover;
            border-radius: 16px;
            background: #e7edf8;
            display: block;
            margin-bottom: .85rem;
        }
        .article {
            border: 1px solid var(--line);
            border-radius: 20px;
            background: var(--card);
            padding: clamp(1rem, 2.5vw, 1.8rem);
            line-height: 1.8;
            box-shadow: 0 12px 28px rgba(16, 36, 63, .06);
        }
        .article h2, .article h3 {
            color: #102f56;
            line-height: 1.35;
            margin-top: 1.4rem;
        }
        .article p, .article li {
            color: #33485f;
        }
        .tags {
            margin-top: 1.2rem;
            display: flex;
            gap: .45rem;
            flex-wrap: wrap;
        }
        .tag {
            font-size: .72rem;
            padding: .2rem .55rem;
            border-radius: 999px;
            background: var(--soft);
            color: #245a9b;
            border: 1px solid #c8dcfb;
        }
        .related {
            margin-top: 1rem;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: #fff;
            padding: 1rem;
        }
        .related h3 {
            margin: 0 0 .7rem;
            color: #19365b;
        }
        .related-list {
            display: grid;
            gap: .45rem;
        }
        .related a {
            text-decoration: none;
            color: var(--primary);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <main class="wrap">
        <a class="back" href="{{ route('cms.company.blog.index', ['etablissementId' => $etablissement->id]) }}">&larr; Retour au blog</a>

        <header class="hero">
            @if($post->featured_image)
                <img class="hero-img" src="{{ $post->featured_image }}" alt="{{ $post->title }}">
            @endif

            <h1>{{ $post->title }}</h1>
            <div class="meta">
                Publie le {{ optional($post->published_at)->format('d/m/Y H:i') ?? optional($post->created_at)->format('d/m/Y H:i') }} · {{ $post->reading_time }} min de lecture
            </div>
        </header>

        <article class="article">
            {!! $post->content !!}

            @if(!empty($post->tags))
                <div class="tags">
                    @foreach($post->tags as $tag)
                        <span class="tag">#{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
        </article>

        @if($relatedPosts->isNotEmpty())
            <aside class="related">
                <h3>Articles similaires</h3>
                <div class="related-list">
                    @foreach($relatedPosts as $related)
                        <a href="{{ route('cms.company.blog.show', ['etablissementId' => $etablissement->id, 'slug' => $related->slug]) }}">{{ $related->title }}</a>
                    @endforeach
                </div>
            </aside>
        @endif
    </main>
</body>
</html>
