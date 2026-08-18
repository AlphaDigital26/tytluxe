<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - TYT Luxe</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: #f4f1eb; color: #333; }
        .wrapper { max-width: 600px; margin: 40px auto; padding: 20px; }
        .card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,0,0,0.10); }

        /* Header */
        .header { background: #0a0a0a; padding: 36px 40px; text-align: center; }
        .header-logo { font-family: 'Cormorant Garamond', serif; font-size: 28px; font-weight: 600; color: #d4af37; letter-spacing: 3px; text-transform: uppercase; }
        .header-tagline { font-size: 11px; color: #888; letter-spacing: 2px; text-transform: uppercase; margin-top: 4px; }

        /* Gold divider */
        .gold-bar { height: 3px; background: linear-gradient(90deg, transparent, #d4af37, transparent); }

        /* Body */
        .body { padding: 44px 48px; }
        .greeting { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600; color: #111; margin-bottom: 14px; }
        .intro { font-size: 14px; line-height: 1.75; color: #555; margin-bottom: 32px; }

        /* OTP Box */
        .otp-container { text-align: center; margin: 32px 0; }
        .otp-label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #999; margin-bottom: 14px; }
        .otp-box { display: inline-block; background: #0a0a0a; border-radius: 10px; padding: 22px 48px; position: relative; }
        .otp-code { font-family: 'Courier New', monospace; font-size: 42px; font-weight: 700; color: #d4af37; letter-spacing: 12px; }
        .otp-expiry { font-size: 12px; color: #888; margin-top: 16px; }
        .otp-expiry strong { color: #d4af37; }

        /* Info box */
        .info-box { background: #fdf9ee; border-left: 3px solid #d4af37; border-radius: 6px; padding: 14px 18px; margin: 28px 0; }
        .info-box p { font-size: 13px; color: #666; line-height: 1.6; }
        .info-box p strong { color: #333; }

        /* Footer */
        .footer { background: #f9f7f3; padding: 28px 48px; text-align: center; border-top: 1px solid #ece8de; }
        .footer p { font-size: 12px; color: #aaa; line-height: 1.7; }
        .footer a { color: #d4af37; text-decoration: none; }
        .footer-brand { font-family: 'Cormorant Garamond', serif; font-size: 16px; font-weight: 600; color: #bbb; letter-spacing: 2px; display: block; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">

            <!-- Header -->
            <div class="header">
                <div class="header-logo">TYT Luxe</div>
                <div class="header-tagline">Elevate Your Journey</div>
            </div>
            <div class="gold-bar"></div>

            <!-- Body -->
            <div class="body">
                <p class="greeting">Hello, {{ $userName }}</p>
                <p class="intro">
                    Thank you for creating your TYT Luxe account. To complete your registration,
                    please use the verification code below. This code confirms your email address
                    and activates your account.
                </p>

                <!-- OTP Display -->
                <div class="otp-container">
                    <div class="otp-label">Your Verification Code</div>
                    <div class="otp-box">
                        <div class="otp-code">{{ $otp }}</div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="info-box">
                    <p>
                        <strong>Security Notice:</strong> Do not share this code with anyone.
                        TYT Luxe will never ask for your OTP via phone or email.
                        If you did not request this, you can safely ignore this email.
                    </p>
                </div>

                <p style="font-size: 13px; color: #888; text-align: center;">
                    This code is valid for one use only.
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <span class="footer-brand">TYT Luxe</span>
                <p>
                    © {{ date('Y') }} TYT Luxe. All rights reserved.<br>
                    You received this email because an account was created with this address.
                </p>
            </div>

        </div>
    </div>
</body>
</html>
