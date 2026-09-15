@extends('layouts.frontend')

@section('meta_title', $post->title . ' | TYT Luxe Travel Journal')
@section('meta_description', Str::limit(strip_tags($post->excerpt ?? $post->body), 160))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
  --cream: #f9f6f1;
  --dark: #0f0c08;
  --gold: #b8935a;
  --text-dark: #1a1108;
  --text-muted: #666;
  --border: rgba(0,0,0,0.08);
}

.post-hero {
  position: relative;
  height: 56vh;
  min-height: 380px;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: flex-end;
}
.post-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: linear-gradient(to bottom, rgba(15,12,8,0.25) 0%, rgba(15,12,8,0.9) 100%);
}
.post-hero-inner {
  position: relative;
  z-index: 1;
  max-width: 860px;
  margin: 0 auto;
  padding: 0 24px 56px;
  width: 100%;
}
.post-category-tag {
  display: inline-block;
  background: var(--gold);
  color: #fff;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 5px 14px;
  border-radius: 20px;
  margin-bottom: 16px;
}
.post-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.8rem, 4vw, 2.6rem);
  color: #fff;
  line-height: 1.25;
  margin-bottom: 16px;
}
.post-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  color: rgba(255,255,255,0.8);
  font-size: 0.9rem;
}
.post-meta span { display: flex; align-items: center; gap: 6px; }

.post-body-wrap {
  max-width: 780px;
  margin: 0 auto;
  padding: 56px 24px 20px;
}
.post-excerpt {
  font-family: 'Playfair Display', serif;
  font-style: italic;
  font-size: 1.2rem;
  color: var(--text-dark);
  border-left: 3px solid var(--gold);
  padding-left: 20px;
  margin-bottom: 32px;
}
.post-body {
  font-size: 1.05rem;
  line-height: 1.85;
  color: var(--text-dark);
}
.post-body p { margin-bottom: 1.4em; }
.post-body h2, .post-body h3 {
  font-family: 'Playfair Display', serif;
  color: var(--text-dark);
  margin: 1.6em 0 0.6em;
}
.post-body img { max-width: 100%; border-radius: 10px; margin: 1.4em 0; }
.post-body a { color: var(--gold); }
.post-body ul, .post-body ol { margin: 0 0 1.4em 1.4em; }

.back-to-blog {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--gold);
  text-decoration: none;
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.related-strip {
  max-width: 1200px;
  margin: 20px auto 60px;
  padding: 40px 24px 0;
  border-top: 2px solid var(--border);
}
.related-strip h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  color: var(--text-dark);
  margin-bottom: 24px;
}
.related-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}
@media (max-width: 768px) { .related-grid { grid-template-columns: 1fr; } }
.related-card {
  text-decoration: none;
  display: block;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.related-card img { width: 100%; height: 160px; object-fit: cover; }
.related-card-body { padding: 16px; background: #fff; }
.related-card-title {
  font-family: 'Playfair Display', serif;
  font-size: 1rem;
  color: var(--text-dark);
  line-height: 1.4;
}
</style>
@endpush

@section('content')

<section class="post-hero" style="background-image: url('{{ $post->resolved_cover_image ?: 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1920&q=80' }}');">
  <div class="post-hero-inner">
    @if($post->category)
      <span class="post-category-tag">{{ $post->category->name }}</span>
    @endif
    <h1 class="post-title">{{ $post->title }}</h1>
    <div class="post-meta">
      @if($post->published_at)
        <span>
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          {{ $post->published_at->format('M j, Y') }}
        </span>
      @endif
      <span>
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        {{ $post->read_time_minutes }} min read
      </span>
    </div>
  </div>
</section>

<div class="post-body-wrap">
  <a href="{{ route('blog') }}" class="back-to-blog">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Back to Travel Journal
  </a>

  @if($post->excerpt)
    <p class="post-excerpt">{{ $post->excerpt }}</p>
  @endif

  {{-- Body is admin-authored rich content sanitized through HTMLPurifier — never render raw. --}}
  <div class="post-body">{!! clean($post->body, 'blog_post') !!}</div>
</div>

@if($relatedPosts->isNotEmpty())
<section class="related-strip">
  <h2>More Stories</h2>
  <div class="related-grid">
    @foreach($relatedPosts as $related)
      <a href="{{ route('blog.details', $related->slug) }}" class="related-card">
        <img src="{{ $related->resolved_cover_image ?: 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=600&q=80' }}" alt="{{ $related->title }}" loading="lazy">
        <div class="related-card-body">
          <h3 class="related-card-title">{{ $related->title }}</h3>
        </div>
      </a>
    @endforeach
  </div>
</section>
@endif

@endsection
