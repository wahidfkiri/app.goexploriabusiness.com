<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - {{ $etablissement->name }}</title>
    <meta name="description" content="Blog officiel de {{ $etablissement->name }}">
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
            max-width: 1140px;
            margin: 0 auto;
            padding: 22px;
        }
        .hero {
            border: 1px solid var(--line);
            border-radius: 24px;
            background: linear-gradient(140deg, #ffffff, #eef5ff 70%);
            padding: clamp(1.2rem, 2vw, 2rem);
        }
        .hero h1 {
            margin: 0;
            font-size: clamp(1.6rem, 3vw, 2.3rem);
            letter-spacing: .2px;
        }
        .hero p {
            margin: .55rem 0 1rem;
            color: var(--muted);
            max-width: 760px;
        }
        .search {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: .6rem;
        }
        .search input {
            width: 100%;
            border-radius: 12px;
            border: 1px solid var(--line);
            padding: .78rem .85rem;
            font-size: .95rem;
            background: #fff;
        }
        .search button {
            border-radius: 12px;
            border: 0;
            padding: .78rem 1rem;
            background: var(--primary);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .grid {
            margin-top: 1.25rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 1rem;
        }
        .card {
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow: hidden;
            background: var(--card);
            display: flex;
            flex-direction: column;
            min-height: 100%;
            box-shadow: 0 10px 25px rgba(16, 36, 63, .05);
        }
        .cover {
            width: 100%;
            height: 190px;
            object-fit: cover;
            display: block;
            background: #e7edf8;
        }
        .card-body {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: .7rem;
            flex: 1;
        }
        .card h3 {
            margin: 0;
            line-height: 1.35;
            font-size: 1.04rem;
        }
        .card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
            font-size: .92rem;
        }
        .meta {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            color: #708198;
            font-size: .82rem;
        }
        .tags {
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
        .read-link {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            text-decoration: none;
            color: var(--primary);
            font-weight: 700;
            font-size: .9rem;
        }
        .empty {
            grid-column: 1 / -1;
            text-align: center;
            border: 1px dashed var(--line);
            border-radius: 14px;
            padding: 2rem;
            color: var(--muted);
            background: #fff;
        }
        .pager {
            margin-top: 1rem;
        }
        @media (max-width: 640px) {
            .search {
                grid-template-columns: 1fr;
            }
            .search button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="hero">
            <h1>Blog de {{ $etablissement->name }}</h1>
            <p>Conseils, tendances et contenus experts pour developper votre activite avec une strategie digitale solide.</p>
            <form class="search" method="GET">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un article, une idee, un sujet...">
                <button type="submit">Rechercher</button>
            </form>
        </section>

        <section class="grid">
            @forelse($posts as $post)
                <article class="card">
                    @if($post->featured_image)
                        <img class="cover" src="{{ $post->featured_image }}" alt="{{ $post->title }}">
                    @else
                        <div class="cover"></div>
                    @endif
                    <div class="card-body">
                        <h3>{{ $post->title }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 140) }}</p>

                        @if(!empty($post->tags))
                            <div class="tags">
                                @foreach($post->tags as $tag)
                                    <span class="tag">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="meta">
                            <span>{{ optional($post->published_at)->format('d/m/Y') ?? '-' }}</span>
                            <span>{{ $post->reading_time }} min</span>
                        </div>

                        <a class="read-link" href="{{ route('cms.company.blog.show', ['etablissementId' => $etablissement->id, 'slug' => $post->slug]) }}">
                            Lire l'article <span>&rarr;</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="empty">Aucun article publie actuellement.</div>
            @endforelse
        </section>

        @if(method_exists($posts, 'links'))
            <div class="pager">{{ $posts->links() }}</div>
        @endif
    </main>
</body>
</html>
