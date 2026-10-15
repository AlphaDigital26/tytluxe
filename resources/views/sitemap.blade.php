{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($pages as $page)
  <url><loc>{{ $page['url'] }}</loc><lastmod>{{ $page['lastmod'] }}</lastmod></url>
@endforeach
@foreach ($hotels as $hotel)
  <url><loc>{{ route('hotel.details', $hotel->slug) }}</loc>@if ($hotel->updated_at)<lastmod>{{ $hotel->updated_at->toDateString() }}</lastmod>@endif</url>
@endforeach
@foreach ($packages as $package)
  <url><loc>{{ route('package.details', $package->slug) }}</loc>@if ($package->updated_at)<lastmod>{{ $package->updated_at->toDateString() }}</lastmod>@endif</url>
@endforeach
@foreach ($posts as $post)
  <url><loc>{{ route('blog.details', $post->slug) }}</loc>@if ($post->updated_at)<lastmod>{{ $post->updated_at->toDateString() }}</lastmod>@endif</url>
@endforeach
</urlset>
