@extends('layouts.frontend')

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
  --cream: #f5f0e8;
  --dark: #0f0c08;
  --dark-mid: #1a1510;
  --gold: #b8935a;
  --gold-hover: #a17f4b;
  --text-dark: #1a1108;
  --text-muted: #666;
  --border-light: rgba(184,147,90,0.2);
}
body {
  font-family: 'Poppins', sans-serif;
  background: var(--cream);
  color: var(--text-dark);
}

/* Page Header */
.page-header {
  background: linear-gradient(rgba(15, 12, 8, 0.7), rgba(15, 12, 8, 0.9)), url('https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
  padding: 180px 24px 140px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.page-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(3rem, 6vw, 4.5rem);
  font-weight: 700;
  color: #fff;
  margin-bottom: 16px;
  position: relative;
  z-index: 2;
}
.page-subtitle {
  font-size: 1rem;
  color: var(--gold);
  letter-spacing: .2em;
  text-transform: uppercase;
  margin-bottom: 8px;
  position: relative;
  z-index: 2;
}

/* Featured Post */
.featured-post-wrapper {
  max-width: 1200px;
  margin: -60px auto 60px;
  padding: 0 24px;
  position: relative;
  z-index: 10;
}
.featured-post {
  display: flex;
  flex-direction: column;
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}
@media (min-width: 768px) {
  .featured-post {
    flex-direction: row;
  }
}
.featured-img-wrap {
  flex: 1;
  min-height: 350px;
  position: relative;
  overflow: hidden;
}
.featured-img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  position: absolute;
  top: 0;
  left: 0;
  transition: transform 0.6s ease;
}
.featured-post:hover .featured-img-wrap img {
  transform: scale(1.05);
}
.featured-content {
  flex: 1;
  padding: 40px 32px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.post-category {
  font-size: 0.85rem;
  color: var(--gold);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-weight: 600;
  margin-bottom: 12px;
}
.featured-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.8rem, 3vw, 2.5rem);
  color: var(--text-dark);
  margin-bottom: 16px;
  line-height: 1.2;
}
.featured-excerpt {
  color: var(--text-muted);
  line-height: 1.7;
  margin-bottom: 24px;
  font-size: 1rem;
}
.read-more-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--dark);
  text-decoration: none;
  transition: color 0.3s ease;
}
.read-more-btn:hover {
  color: var(--gold);
}
.read-more-btn svg {
  transition: transform 0.3s ease;
}
.read-more-btn:hover svg {
  transform: translateX(4px);
}

/* Blog Layout */
.blog-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 24px 100px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 60px;
}
@media (min-width: 992px) {
  .blog-container {
    grid-template-columns: 2fr 1fr;
  }
}

/* Grid Posts */
.blog-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 40px;
}
@media (min-width: 768px) {
  .blog-grid {
    grid-template-columns: 1fr 1fr;
  }
}
.blog-card {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.04);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
}
.blog-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 40px rgba(0,0,0,0.08);
}
.blog-card-img {
  width: 100%;
  height: 240px;
  object-fit: cover;
  transition: transform 0.5s ease;
}
.blog-card:hover .blog-card-img {
  transform: scale(1.05);
}
.blog-card-img-wrap {
  overflow: hidden;
}
.blog-card-content {
  padding: 24px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.blog-card-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.4rem;
  color: var(--text-dark);
  margin: 12px 0 16px;
  line-height: 1.3;
}
.blog-card-excerpt {
  color: var(--text-muted);
  font-size: 0.95rem;
  line-height: 1.6;
  margin-bottom: 24px;
  flex: 1;
}

/* Sidebar */
.sidebar {
  display: flex;
  flex-direction: column;
  gap: 40px;
}
.widget {
  background: #fff;
  padding: 32px;
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.03);
}
.widget-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  color: var(--text-dark);
  margin-bottom: 24px;
  position: relative;
  padding-bottom: 12px;
}
.widget-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 40px;
  height: 2px;
  background: var(--gold);
}

/* Newsletter Widget */
.sidebar .newsletter-form {
  display: block !important;
}
.sidebar .newsletter-form form {
  display: flex;
  flex-direction: column;
}
.sidebar .newsletter-form p {
  color: var(--text-muted);
  font-size: 0.95rem;
  margin-bottom: 20px;
}
.sidebar .newsletter-form input {
  width: 100%;
  padding: 14px 16px;
  border: 1px solid rgba(184, 147, 90, 0.4);
  border-radius: 6px;
  margin-bottom: 16px;
  font-family: 'Poppins', sans-serif;
  outline: none;
  background-color: var(--cream);
  color: var(--text-dark);
  transition: all 0.3s ease;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
}
.sidebar .newsletter-form input:focus {
  border-color: var(--gold);
  background-color: #fff;
  box-shadow: 0 0 0 3px rgba(184, 147, 90, 0.2);
}
.btn-gold {
  display: inline-block;
  width: 100%;
  padding: 16px 24px;
  background: linear-gradient(135deg, var(--gold) 0%, #d4b581 100%);
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: 'Poppins', sans-serif;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  box-shadow: 0 4px 15px rgba(184, 147, 90, 0.3);
}
.btn-gold:hover {
  background: linear-gradient(135deg, #a17f4b 0%, var(--gold) 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(184, 147, 90, 0.4);
}

/* Categories Widget */
.category-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.category-list li {
  margin-bottom: 12px;
}
.category-list li:last-child {
  margin-bottom: 0;
}
.category-list a {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: var(--text-muted);
  text-decoration: none;
  font-size: 0.95rem;
  transition: color 0.3s, padding-left 0.3s;
}
.category-list a:hover {
  color: var(--gold);
  padding-left: 5px;
}
.cat-count {
  background: var(--cream);
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 0.8rem;
  color: var(--dark-mid);
}
</style>
@endpush

@section('content')
<div class="page-header">
  <div class="page-subtitle">TYT Luxe</div>
  <h1 class="page-title">Travel Journal</h1>
</div>

<!-- Featured Post -->
<div class="featured-post-wrapper">
  <article class="featured-post">
    <div class="featured-img-wrap">
      <img src="https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1400&q=80" alt="Luxury Villa in Maldives">
    </div>
    <div class="featured-content">
      <span class="post-category">Destination Guide</span>
      <h2 class="featured-title">10 Hidden Gems in the Maldives for Your Next Staycation</h2>
      <p class="featured-excerpt">Escape the ordinary and discover pristine private islands, underwater dining experiences, and secluded overwater bungalows that offer the ultimate luxury retreat away from the crowds.</p>
      <a href="{{ route('blog.details') }}" class="read-more-btn">
        Read Article
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
      </a>
    </div>
  </article>
</div>

<div class="blog-container">
  
  <!-- Main Content -->
  <main class="blog-main">
    <div class="blog-grid">
      
      <!-- Post 1 -->
      <article class="blog-card">
        <div class="blog-card-img-wrap">
          <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Paris Streets" class="blog-card-img">
        </div>
        <div class="blog-card-content">
          <span class="post-category">Culinary Journeys</span>
          <h3 class="blog-card-title">A Taste of Elegance: Dining Through Paris</h3>
          <p class="blog-card-excerpt">From hidden Michelin-starred bistros to the finest patisseries in Montmartre, explore the culinary wonders of the French capital.</p>
          <a href="{{ route('blog.details') }}" class="read-more-btn">Read Article <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg></a>
        </div>
      </article>

      <!-- Post 2 -->
      <article class="blog-card">
        <div class="blog-card-img-wrap">
          <img src="https://images.unsplash.com/photo-1599640842225-85d111c60e6b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Luxury Cruise" class="blog-card-img">
        </div>
        <div class="blog-card-content">
          <span class="post-category">Travel Tips</span>
          <h3 class="blog-card-title">The Ultimate Packing List for a Luxury Cruise</h3>
          <p class="blog-card-excerpt">Ensure you have everything you need for a spectacular voyage, from formal evening wear to effortless daytime excursion outfits.</p>
          <a href="{{ route('blog.details') }}" class="read-more-btn">Read Article <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg></a>
        </div>
      </article>

      <!-- Post 3 -->
      <article class="blog-card">
        <div class="blog-card-img-wrap">
          <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Bali Retreat" class="blog-card-img">
        </div>
        <div class="blog-card-content">
          <span class="post-category">Wellness</span>
          <h3 class="blog-card-title">Top 5 Wellness Retreats in Bali to Rejuvenate</h3>
          <p class="blog-card-excerpt">Find inner peace and holistic healing in these exclusive, lush jungle sanctuaries located in the spiritual heart of Bali.</p>
          <a href="{{ route('blog.details') }}" class="read-more-btn">Read Article <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg></a>
        </div>
      </article>

      <!-- Post 4 -->
      <article class="blog-card">
        <div class="blog-card-img-wrap">
          <img src="https://images.unsplash.com/photo-1491555103944-7c647fd857e6?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Swiss Alps" class="blog-card-img">
        </div>
        <div class="blog-card-content">
          <span class="post-category">Adventure</span>
          <h3 class="blog-card-title">Exploring the Swiss Alps: A Winter Guide</h3>
          <p class="blog-card-excerpt">Discover the most exclusive ski resorts, cozy chalets, and breathtaking alpine experiences in Switzerland this winter.</p>
          <a href="{{ route('blog.details') }}" class="read-more-btn">Read Article <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg></a>
        </div>
      </article>

    </div>
  </main>

  <!-- Sidebar -->
  <aside class="sidebar">
    
    <div class="widget">
      <h3 class="widget-title">Join Our Newsletter</h3>
      <div class="newsletter-form">
        <p>Get exclusive travel offers, destination guides, and insider tips delivered straight to your inbox.</p>
        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Subscribed successfully!');">
          <input type="email" placeholder="Your Email Address" required>
          <button type="submit" class="btn-gold">Subscribe Now</button>
        </form>
      </div>
    </div>

    <div class="widget">
      <h3 class="widget-title">Categories</h3>
      <ul class="category-list">
        <li><a href="#">Destination Guides <span class="cat-count">12</span></a></li>
        <li><a href="#">Luxury Staycations <span class="cat-count">8</span></a></li>
        <li><a href="#">Culinary Journeys <span class="cat-count">5</span></a></li>
        <li><a href="#">Travel Tips & Hacks <span class="cat-count">14</span></a></li>
        <li><a href="#">Wellness Retreats <span class="cat-count">7</span></a></li>
        <li><a href="#">Cruises & Yachts <span class="cat-count">4</span></a></li>
      </ul>
    </div>

  </aside>
</div>
@endsection
