<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $blogPost->title }} - TYT Luxe</title>
    <style>
        body { font-family: 'Playfair Display', serif, sans-serif; background-color: #f9f9f9; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 30px; }
        .header img { max-width: 150px; }
        h1 { color: #111; font-size: 28px; margin-bottom: 15px; }
        .image-container { width: 100%; height: auto; text-align: center; margin-bottom: 20px; }
        .image-container img { max-width: 100%; border-radius: 8px; }
        p { font-size: 16px; line-height: 1.6; color: #555; }
        .button { display: inline-block; padding: 12px 25px; margin-top: 20px; background-color: #d4af37; color: #fff; text-decoration: none; font-weight: bold; border-radius: 4px; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #aaa; border-top: 1px solid #eee; padding-top: 20px; }
        .footer a { color: #aaa; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>TYT Luxe News</h2>
        </div>
        
        @if($blogPost->image)
        <div class="image-container">
            <img src="{{ url('storage/' . $blogPost->image) }}" alt="{{ $blogPost->title }}">
        </div>
        @endif

        <h1>{{ $blogPost->title }}</h1>
        
        <p>{{ Str::limit(strip_tags($blogPost->content), 150) }}</p>
        
        <div style="text-align: center;">
            <a href="{{ url('/blog/' . $blogPost->slug) }}" class="button">Read Full Story</a>
        </div>

        <div class="footer">
            <p>You received this email because you subscribed to our newsletter.</p>
        </div>
    </div>
</body>
</html>
