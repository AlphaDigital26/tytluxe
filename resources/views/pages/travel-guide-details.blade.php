@extends('layouts.frontend')

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
  --cream: #f5f0e8;
  --dark: #0f0c08;
  --dark-mid: #1a1510;
  --gold: #b8935a;
  --text-dark: #1a1108;
  --text-muted: #666;
  --border-light: rgba(184,147,90,0.2);
}
body {
  font-family: 'Poppins', sans-serif;
  background: var(--cream);
  color: var(--text-dark);
}

.blog-hero {
  position: relative;
  height: 60vh;
  min-height: 450px;
  background-image: linear-gradient(rgba(15, 12, 8, 0.4), rgba(15, 12, 8, 0.7)), url('https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 120px 24px 0; /* Add top padding to clear navbar */
}
.blog-hero-content {
  max-width: 800px;
  /* Removed margin-top since we handle it in parent padding */
}
.blog-category {
  color: var(--gold);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 16px;
  display: block;
}
.blog-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(2.5rem, 5vw, 4rem);
  color: #fff;
  line-height: 1.2;
  margin-bottom: 24px;
}
.blog-meta {
  color: rgba(255,255,255,0.8);
  font-size: 0.95rem;
}

.blog-content-wrapper {
  max-width: 800px;
  margin: -40px auto 100px;
  background: #fff;
  padding: 60px;
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.05);
  position: relative;
  z-index: 10;
}
.blog-content {
  font-size: 1.05rem;
  line-height: 1.8;
  color: var(--text-muted);
}
.blog-content h2, .blog-content h3 {
  font-family: 'Playfair Display', serif;
  color: var(--text-dark);
  margin-top: 40px;
  margin-bottom: 16px;
}
.blog-content p {
  margin-bottom: 24px;
}
.blog-content img {
  width: 100%;
  border-radius: 8px;
  margin: 32px 0;
}
.blog-content blockquote {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-style: italic;
  color: var(--gold);
  border-left: 4px solid var(--gold);
  margin: 40px 0;
  padding-left: 24px;
}
.back-link {
  display: inline-block;
  margin-top: 40px;
  color: var(--dark-mid);
  text-decoration: none;
  font-weight: 600;
  border-bottom: 1px solid var(--gold);
  padding-bottom: 4px;
  transition: color 0.3s;
}
.back-link:hover {
  color: var(--gold);
}
</style>
@endpush

@section('content')
<div class="blog-hero">
  <div class="blog-hero-content">
    <span class="blog-category">Destination Guide</span>
    <h1 class="blog-title">10 Hidden Gems in the Maldives for Your Next Staycation</h1>
    <div class="blog-meta">By TYT Luxe Experts &nbsp;|&nbsp; August 06, 2026</div>
  </div>
</div>

<div class="blog-content-wrapper">
  <div class="blog-content">
    <p>The Maldives has long been synonymous with luxury travel, characterized by crystal-clear turquoise waters, powdery white sand beaches, and iconic overwater bungalows. However, beyond the well-trodden paths of popular resorts lie hidden gems waiting to be explored by discerning travelers seeking exclusivity and untouched beauty.</p>
    
    <h2>1. The Secluded Private Sandbanks</h2>
    <p>Imagine having an entire stretch of sand completely to yourself, surrounded by nothing but the gentle lull of the Indian Ocean. Many boutique luxury resorts now offer private sandbank dining experiences, where you are whisked away by a private speedboat to enjoy a bespoke meal under the stars, completely secluded from the rest of the world.</p>
    
    <img src="https://images.unsplash.com/photo-1514282401047-d79a71a590e8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Maldives Sandbank">
    
    <h2>2. Underwater Culinary Journeys</h2>
    <p>While dining by the beach is a staple of tropical vacations, the Maldives takes it a step further with exclusive underwater restaurants. Descend beneath the waves to enjoy a multi-course gourmet meal while vibrant marine life swims past panoramic glass walls. It's a surreal experience that redefines luxury dining.</p>
    
    <blockquote>"To travel is to discover that everyone is wrong about other countries, but in the Maldives, every postcard-perfect expectation is beautifully exceeded."</blockquote>

    <h2>3. Eco-Luxury Sanctuaries</h2>
    <p>Sustainable travel is becoming a priority for luxury travelers. Discover hidden resorts that are fully solar-powered, utilize organic gardens for their fine dining restaurants, and actively participate in coral propagation projects. You can enjoy the pinnacle of luxury while knowing your footprint is minimal.</p>
    
    <p>Ready to discover these hidden gems for yourself? Speak to our luxury travel curators today to craft your perfect, personalized Maldivian staycation.</p>
    
    <a href="{{ route('blog') }}" class="back-link">← Back to Travel Journal</a>
  </div>
</div>
@endsection
