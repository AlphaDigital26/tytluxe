<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Adventure Itinerary</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff;
            color: #333333;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Wanderon Inspired Colors */
        .text-brand-blue { color: #007bff; }
        .bg-brand-blue { background-color: #007bff; }
        .text-brand-yellow { color: #ffb800; }
        .bg-brand-yellow { background-color: #ffb800; }
        
        .page-break {
            page-break-before: always;
        }

        /* A4 Page constraints for Spatie PDF */
        @page {
            margin: 0;
            size: A4;
        }
        
        .page {
            width: 210mm;
            min-height: 297mm;
            position: relative;
            background: white;
            overflow: hidden;
            box-sizing: border-box;
        }

        .hero-clip {
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
        }
        
        .timeline-line {
            width: 3px;
            background-color: #007bff;
            position: absolute;
            left: 24px;
            top: 40px;
            bottom: -40px;
            z-index: 0;
        }
    </style>
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Cover Page -->
    <div class="page bg-[#f9fafc]">
        
        <!-- Hero Section -->
        <div class="relative h-[65%] w-full hero-clip">
            <img src="https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80" class="w-full h-full object-cover" alt="Travel Cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
            
            <!-- Sleek Logo Badge -->
            <div class="absolute top-8 left-10 bg-white/95 backdrop-blur-md px-5 py-3 rounded-2xl shadow-2xl border border-white/50 flex items-center justify-center">
                @php
                    $logoPath = public_path('assets/images/tyt-logo.png');
                    $logoData = base64_encode(file_get_contents($logoPath));
                    $logoSrc = 'data:image/png;base64,' . $logoData;
                @endphp
                <img src="{{ $logoSrc }}" alt="TYT Luxe Logo" class="h-14 w-auto object-contain drop-shadow-sm">
            </div>

            <div class="absolute bottom-16 left-10">
                <span class="bg-brand-yellow text-black font-bold text-xs uppercase px-3 py-1 rounded-full mb-3 inline-block shadow-lg">Group Trip</span>
                <h1 class="text-white text-6xl font-extrabold leading-tight shadow-sm">Explore Bali<br>The Island of Gods</h1>
                <p class="text-white/90 text-xl font-medium mt-4 flex items-center gap-2">
                    <i class="fa-solid fa-clock text-brand-yellow"></i> 5 Nights / 6 Days
                </p>
            </div>
        </div>

        <!-- Highlights Row -->
        <div class="px-10 py-10">
            <div class="bg-white rounded-2xl shadow-xl p-8 -mt-20 relative z-10 flex justify-between items-center border border-gray-100">
                
                <div class="text-center w-1/4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-brand-blue flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-solid fa-plane-departure"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-sm">Starts At</h3>
                    <p class="text-gray-500 text-xs mt-1">Denpasar Airport</p>
                </div>
                
                <div class="w-px h-16 bg-gray-200"></div>
                
                <div class="text-center w-1/4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-brand-blue flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-sm">Destinations</h3>
                    <p class="text-gray-500 text-xs mt-1">Ubud, Seminyak, Kuta</p>
                </div>

                <div class="w-px h-16 bg-gray-200"></div>

                <div class="text-center w-1/4">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-brand-blue flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-solid fa-tag"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-sm">Starting Price</h3>
                    <p class="text-gray-500 text-xs mt-1 text-brand-blue font-bold">$1,200</p>
                </div>

            </div>

            <div class="mt-12 text-center">
                <h2 class="text-3xl font-extrabold text-gray-800 mb-4">Are you ready for the adventure?</h2>
                <p class="text-gray-600 px-10 text-sm leading-relaxed">
                    Experience the perfect blend of rich culture, breathtaking landscapes, and pristine beaches. From the lush rice terraces of Ubud to the vibrant nightlife of Seminyak, this meticulously planned trip covers the absolute best of Bali.
                </p>
            </div>
        </div>
    </div>

    <!-- Brief Itinerary & Inclusions -->
    <div class="page p-10 page-break bg-white">
        
        <div class="flex gap-10">
            <!-- Left: Brief Itinerary -->
            <div class="w-[55%] bg-gray-50 rounded-2xl p-8 border border-gray-100 shadow-sm">
                <h2 class="text-2xl font-extrabold text-gray-800 flex items-center gap-3 mb-6">
                    <i class="fa-solid fa-list-check text-brand-blue"></i> Brief Itinerary
                </h2>
                
                <ul class="space-y-4">
                    <li class="flex items-start gap-4">
                        <div class="bg-brand-blue text-white text-xs font-bold px-2 py-1 rounded">Day 1</div>
                        <div class="text-sm font-semibold text-gray-700 pt-0.5">Arrival in Bali. Transfer to Ubud.</div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="bg-brand-blue text-white text-xs font-bold px-2 py-1 rounded">Day 2</div>
                        <div class="text-sm font-semibold text-gray-700 pt-0.5">Ubud Full Day Tour (Kintamani & Swing).</div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="bg-brand-blue text-white text-xs font-bold px-2 py-1 rounded">Day 3</div>
                        <div class="text-sm font-semibold text-gray-700 pt-0.5">Transfer to Seminyak & Sunset Cruise.</div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="bg-brand-blue text-white text-xs font-bold px-2 py-1 rounded">Day 4</div>
                        <div class="text-sm font-semibold text-gray-700 pt-0.5">Nusa Penida Island Hopping.</div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="bg-brand-blue text-white text-xs font-bold px-2 py-1 rounded">Day 5</div>
                        <div class="text-sm font-semibold text-gray-700 pt-0.5">Leisure Day & Farewell Dinner.</div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="bg-brand-blue text-white text-xs font-bold px-2 py-1 rounded">Day 6</div>
                        <div class="text-sm font-semibold text-gray-700 pt-0.5">Departure. Head back with memories.</div>
                    </li>
                </ul>
            </div>

            <!-- Right: Inclusions & Exclusions -->
            <div class="w-[45%]">
                <h2 class="text-2xl font-extrabold text-gray-800 flex items-center gap-3 mb-6">
                    <i class="fa-solid fa-circle-check text-green-500"></i> Inclusions
                </h2>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="fa-solid fa-check text-green-500"></i> Premium 4-Star Accommodation
                    </li>
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="fa-solid fa-check text-green-500"></i> Daily Breakfast & 2 Dinners
                    </li>
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="fa-solid fa-check text-green-500"></i> Airport Transfers in Private AC Car
                    </li>
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="fa-solid fa-check text-green-500"></i> All Sightseeing & Entry Tickets
                    </li>
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="fa-solid fa-check text-green-500"></i> English Speaking Guide
                    </li>
                </ul>

                <h2 class="text-2xl font-extrabold text-gray-800 flex items-center gap-3 mb-6">
                    <i class="fa-solid fa-circle-xmark text-red-500"></i> Exclusions
                </h2>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="fa-solid fa-xmark text-red-500"></i> International Flights
                    </li>
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="fa-solid fa-xmark text-red-500"></i> Visa Fees (If applicable)
                    </li>
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="fa-solid fa-xmark text-red-500"></i> Personal Expenses
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-12">
             <h2 class="text-3xl font-extrabold text-gray-800 flex items-center gap-3 mb-8">
                <i class="fa-solid fa-route text-brand-blue"></i> Detailed Itinerary
            </h2>

            <div class="relative pl-6">
                <!-- Vertical Line -->
                <div class="absolute left-6 top-6 bottom-0 w-1 bg-gray-200"></div>

                <!-- Day 1 Node -->
                <div class="relative mb-10">
                    <div class="absolute -left-4 top-1 w-8 h-8 bg-brand-blue rounded-full border-4 border-white flex items-center justify-center shadow">
                        <i class="fa-solid fa-plane text-white text-xs"></i>
                    </div>
                    <div class="pl-8">
                        <span class="text-brand-blue font-bold text-sm tracking-widest uppercase">Day 1</span>
                        <h3 class="text-xl font-extrabold text-gray-800 mt-1 mb-3">Arrival & Transfer to Ubud</h3>
                        <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="w-full h-40 object-cover rounded-xl shadow-sm mb-4" alt="Ubud">
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Arrive at Ngurah Rai International Airport. Our representative will meet you and transfer you to your hotel in Ubud. Check-in and spend the rest of the day relaxing or exploring the local markets of Ubud on your own. Overnight stay in Ubud.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Detailed Itinerary Cont. -->
    <div class="page p-10 page-break bg-white">
        
        <div class="relative pl-6">
            <div class="absolute left-6 top-0 bottom-0 w-1 bg-gray-200"></div>

            <!-- Day 2 Node -->
            <div class="relative mb-12">
                <div class="absolute -left-4 top-1 w-8 h-8 bg-brand-yellow rounded-full border-4 border-white flex items-center justify-center shadow">
                    <i class="fa-solid fa-camera text-white text-xs"></i>
                </div>
                <div class="pl-8">
                    <span class="text-brand-yellow font-bold text-sm tracking-widest uppercase">Day 2</span>
                    <h3 class="text-xl font-extrabold text-gray-800 mt-1 mb-3">Kintamani Volcano & Bali Swing</h3>
                    <img src="https://images.unsplash.com/photo-1554481923-a691d7c99f3c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="w-full h-48 object-cover rounded-xl shadow-sm mb-4" alt="Bali Swing">
                    <p class="text-sm text-gray-600 leading-relaxed">
                        After a hearty breakfast, get ready for a full-day tour. Start with the famous Bali Swing, soaring high above the jungle canopy. Then, proceed to Kintamani to enjoy breathtaking views of the active Mount Batur volcano and its crater lake. Return to the hotel by evening.
                    </p>
                </div>
            </div>

            <!-- Day 3 Node -->
            <div class="relative mb-12">
                <div class="absolute -left-4 top-1 w-8 h-8 bg-brand-blue rounded-full border-4 border-white flex items-center justify-center shadow">
                    <i class="fa-solid fa-ship text-white text-xs"></i>
                </div>
                <div class="pl-8">
                    <span class="text-brand-blue font-bold text-sm tracking-widest uppercase">Day 3</span>
                    <h3 class="text-xl font-extrabold text-gray-800 mt-1 mb-3">Seminyak Transfer & Sunset Cruise</h3>
                    <img src="https://images.unsplash.com/photo-1539367628448-4bc5c9d171c8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="w-full h-48 object-cover rounded-xl shadow-sm mb-4" alt="Seminyak Sunset">
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Check out from Ubud and head towards the vibrant area of Seminyak. After checking into your premium resort, take some time to relax. In the late afternoon, embark on a luxurious sunset dinner cruise along the southern coast of Bali.
                    </p>
                </div>
            </div>

            <!-- Ending Node to cap the line beautifully -->
             <div class="relative">
                <div class="absolute -left-4 top-1 w-8 h-8 bg-green-500 rounded-full border-4 border-white flex items-center justify-center shadow">
                    <i class="fa-solid fa-flag-checkered text-white text-xs"></i>
                </div>
                <div class="pl-8">
                    <span class="text-green-500 font-bold text-sm tracking-widest uppercase">And So On...</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Contact & Footer -->
    <div class="page page-break bg-gray-900 text-white relative">
        <img src="https://images.unsplash.com/photo-1544644181-1484b3fdfc62?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80" class="absolute inset-0 w-full h-full object-cover opacity-20 mix-blend-overlay" alt="Footer Bg">
        
        <div class="relative z-10 flex flex-col items-center justify-center h-full p-16 text-center">
            
            <h2 class="text-5xl font-extrabold mb-6">Let's Make It Happen!</h2>
            <p class="text-lg text-gray-300 font-light max-w-xl mx-auto mb-12">
                Your dream vacation is just one step away. Contact our travel experts today to book this itinerary or customize it to your liking.
            </p>

            <div class="bg-white/10 backdrop-blur-md rounded-3xl p-10 w-full max-w-2xl border border-white/20 shadow-2xl flex justify-around">
                
                <div class="text-center">
                    <div class="w-14 h-14 bg-brand-yellow text-black rounded-full flex items-center justify-center mx-auto mb-4 text-2xl shadow-lg">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <p class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-1">Call Us</p>
                    <p class="font-bold text-lg">+91 98765 43210</p>
                </div>

                <div class="w-px bg-white/20"></div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-brand-blue text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl shadow-lg">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <p class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-1">Email Us</p>
                    <p class="font-bold text-lg">hello@tytluxe.com</p>
                </div>

            </div>

            <div class="mt-20">
                @php
                    $logoWhitePath = public_path('assets/images/logo-white.png');
                    $logoDataFooter = base64_encode(file_get_contents($logoWhitePath));
                    $logoSrcFooter = 'data:image/png;base64,' . $logoDataFooter;
                @endphp
                <img src="{{ $logoSrcFooter }}" alt="TYT Luxe Logo" class="h-20 object-contain mx-auto mb-4 opacity-75">
                <p class="text-gray-500 text-xs mt-4 uppercase tracking-widest">© 2026 TYT Luxe. All Rights Reserved.</p>
            </div>
        </div>
    </div>

</body>
</html>
