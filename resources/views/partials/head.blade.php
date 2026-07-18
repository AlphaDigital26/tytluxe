<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>TYT Luxe</title>

{{-- Google Fonts: Required by the theme (Outfit for body, Playfair Display for headings) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:wght@700;900&display=swap">

{{-- Font Awesome --}}
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

{{-- Theme Stylesheet --}}
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">

{{-- Vite (app.js only — provides axios; app.css is intentionally excluded to avoid Tailwind reset conflicts) --}}
@vite(['resources/js/app.js'])

</head>