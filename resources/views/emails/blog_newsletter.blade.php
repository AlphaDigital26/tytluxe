<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $blogPost->title }} - TYT Luxe</title>
</head>
<body style="margin:0; padding:0; background-color:#ffffff; font-family: Georgia, 'Cormorant Garamond', 'Playfair Display', serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff; padding:44px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%;">

                    <!-- Wordmark -->
                    <tr>
                        <td align="center" style="padding:0 0 26px;">
                            <span style="font-family:'Jost', Arial, sans-serif; font-size:12px; letter-spacing:0.3em; color:#a9793c;">&#10022;&nbsp;&nbsp;TYT LUXE&nbsp;&nbsp;&#10022;</span>
                        </td>
                    </tr>

                    <!-- Card -->
                    <tr>
                        <td style="background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #ece2c8;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">

                                <!-- Hero image -->
                                @if($blogPost->resolved_cover_image)
                                <tr>
                                    <td>
                                        <img src="{{ $blogPost->resolved_cover_image }}" alt="{{ $blogPost->title }}" width="600" style="width:100%; max-width:600px; height:270px; object-fit:cover; display:block; background-color:#e9e1cd;">
                                    </td>
                                </tr>
                                @endif

                                <!-- Gold shimmer hairline -->
                                <tr>
                                    <td style="height:4px; line-height:4px; font-size:0; background-color:#c29a62; background-image:linear-gradient(90deg, #a9793c, #f5df9b 30%, #fff3c9 50%, #f5df9b 70%, #a9793c);">&nbsp;</td>
                                </tr>

                                <!-- Body -->
                                <tr>
                                    <td style="padding:36px 36px 6px;" align="center">

                                        <span style="display:inline-block; margin-bottom:18px; font-family:'Jost', Arial, sans-serif; font-size:16px; color:#c29a62;">&#10022;</span>

                                        <!-- Category / meta -->
                                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 20px;">
                                            <tr>
                                                @if($blogPost->category)
                                                <td style="padding:5px 16px; background-color:#2b2116; border-radius:20px;">
                                                    <span style="font-family:'Jost', Arial, sans-serif; font-size:10px; letter-spacing:0.2em; text-transform:uppercase; color:#e8c96b;">{{ $blogPost->category->name }}</span>
                                                </td>
                                                <td style="width:12px;">&nbsp;</td>
                                                @endif
                                                @if($blogPost->read_time_minutes)
                                                <td>
                                                    <span style="font-family:'Jost', Arial, sans-serif; font-size:11px; letter-spacing:0.1em; text-transform:uppercase; color:#9a8f7a;">{{ $blogPost->read_time_minutes }} min read</span>
                                                </td>
                                                @endif
                                            </tr>
                                        </table>

                                        <h1 style="margin:0 0 20px; font-family:'Cormorant Garamond', Georgia, serif; font-size:32px; line-height:1.28; color:#2b2116; font-weight:700; text-align:center;">
                                            {{ $blogPost->title }}
                                        </h1>

                                        <div style="width:44px; height:2px; margin:0 auto 22px; background-color:#c29a62; background-image:linear-gradient(90deg, #c29a62, #e8c96b);"></div>

                                        <p style="margin:0 0 26px; font-family: Georgia, serif; font-size:16px; font-style:italic; line-height:1.8; color:#5b5346; text-align:center;">
                                            {{ $blogPost->excerpt ?: Str::limit(strip_tags($blogPost->body), 180) }}
                                        </p>

                                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
                                            <tr>
                                                <td align="center" style="border-radius:30px; background-color:#c29a62; background-image:linear-gradient(90deg, #b3874d, #e8c96b);">
                                                    <a href="{{ url('/blog/' . $blogPost->slug) }}" style="display:inline-block; padding:14px 36px; font-family:'Jost', Arial, sans-serif; font-size:12px; letter-spacing:0.18em; text-transform:uppercase; color:#241a3d; text-decoration:none; font-weight:700;">
                                                        Read Full Story &nbsp;&#10148;
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Divider -->
                                <tr>
                                    <td style="padding:14px 36px 0;" align="center">
                                        <span style="font-family:'Jost', Arial, sans-serif; font-size:12px; letter-spacing:0.4em; color:#d8cba3;">&#10022; &#10022; &#10022;</span>
                                    </td>
                                </tr>

                                <!-- Footer -->
                                <tr>
                                    <td align="center" style="padding:20px 36px 32px;">
                                        <p style="margin:0 0 12px;">
                                            <a href="{{ url('/') }}" style="font-family:'Jost', Arial, sans-serif; font-size:11px; letter-spacing:0.12em; text-transform:uppercase; color:#a9793c; text-decoration:none;">Home</a>
                                            <span style="color:#cfc4a8; padding:0 8px;">&middot;</span>
                                            <a href="{{ url('/blog') }}" style="font-family:'Jost', Arial, sans-serif; font-size:11px; letter-spacing:0.12em; text-transform:uppercase; color:#a9793c; text-decoration:none;">Blog</a>
                                            <span style="color:#cfc4a8; padding:0 8px;">&middot;</span>
                                            <a href="{{ url('/contact') }}" style="font-family:'Jost', Arial, sans-serif; font-size:11px; letter-spacing:0.12em; text-transform:uppercase; color:#a9793c; text-decoration:none;">Contact</a>
                                        </p>
                                        <p style="margin:0 0 6px; font-family:'Jost', Arial, sans-serif; font-size:11px; line-height:1.6; color:#9a8f7a;">
                                            You received this email because you subscribed to the TYT Luxe newsletter.
                                        </p>
                                        <p style="margin:0; font-family:'Jost', Arial, sans-serif; font-size:11px; color:#b3a98d;">
                                            &copy; {{ date('Y') }} TYT Luxe &nbsp;&middot;&nbsp;
                                            <a href="{{ url('/privacy') }}" style="color:#a9793c; text-decoration:underline;">Privacy Policy</a>
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
