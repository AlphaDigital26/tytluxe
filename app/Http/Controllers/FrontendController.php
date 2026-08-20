<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Cruise;
use App\Models\Setting;
use App\Models\Offer;
use App\Models\Package;
use Illuminate\Support\Facades\Storage;

class FrontendController extends Controller
{
    public function index()
    {
        return view('pages.home');
    }

    public function hotels()
    {
        $hotels = Hotel::with(['destination', 'amenities', 'images'])
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('pages.hotels', compact('hotels'));
    }

    public function hotelDetails($id)
    {
        $hotel = Hotel::with(['destination', 'amenities', 'images', 'roomTypes'])
            ->where('is_active', true)
            ->findOrFail($id);

        return view('pages.hotel-details', compact('hotel'));
    }

    public function cruises()
    {
        // ── Page-level text settings ───────────────────────────────────────
        $s = function (string $key, mixed $default = '') {
            return Setting::get($key, $default);
        };
        $j = function (string $key, mixed $default = []) {
            return Setting::getJson($key, $default);
        };

        // Hero
        $heroEyebrow  = $s('cruise_page.hero_eyebrow',  "Cordelia Cruises · India's Premium Cruise Line");
        $heroTitle    = $s('cruise_page.hero_title',    'Destination of <br><em>Your Dreams</em>');
        $heroSubtitle = $s('cruise_page.hero_subtitle', 'Mumbai &bull; Goa &bull; Kochi &bull; Lakshadweep &bull; Chennai &bull; Sri Lanka');
        $heroCtaText  = $s('cruise_page.hero_cta_text', 'Enquire Now');

        // Ship stats
        $shipStats = $j('cruise_page.ship_stats', [
            ['value' => 'All-Inclusive', 'label' => 'Dining & Entertainment'],
            ['value' => '48,563 GT',     'label' => 'Gross Tonnage'],
            ['value' => '6 Ports',       'label' => 'Mumbai to Sri Lanka'],
            ['value' => '24/7',          'label' => 'Onboard Support'],
        ]);

        // Destinations
        $destinationsLabel   = $s('cruise_page.destinations_label',   'Where We Sail');
        $destinationsHeading = $s('cruise_page.destinations_heading', 'Six Stunning Destinations');
        $destinationCards    = $j('cruise_page.destination_cards', []);

        // Resolve destination card images (uploaded file takes priority)
        $destinationCards = array_map(function ($card) {
            $card['resolved_image'] = (!empty($card['image_path']) && Storage::disk('public')->exists($card['image_path']))
                ? Storage::disk('public')->url($card['image_path'])
                : ($card['image_url'] ?? null);
            return $card;
        }, $destinationCards);

        // Experience Tabs
        $diningIntro        = $s('cruise_page.dining_intro');
        $diningItems        = $j('cruise_page.dining_items', []);
        $entertainmentIntro = $s('cruise_page.entertainment_intro');
        $entertainmentItems = $j('cruise_page.entertainment_items', []);
        $barsIntro          = $s('cruise_page.bars_intro');
        $barsItems          = $j('cruise_page.bars_items', []);
        $indulgenceIntro    = $s('cruise_page.indulgence_intro');
        $indulgenceItems    = $j('cruise_page.indulgence_items', []);
        $eventsItems        = $j('cruise_page.events_items', []);

        // Resolve dining item images
        $diningItems = array_map(function ($item) {
            $item['resolved_image'] = (!empty($item['image_path']) && Storage::disk('public')->exists($item['image_path']))
                ? Storage::disk('public')->url($item['image_path'])
                : ($item['image_url'] ?? null);
            return $item;
        }, $diningItems);

        // Trust strip
        $trustItems = $j('cruise_page.trust_items', []);

        // Booking form options
        $bookingPorts        = array_filter(array_map('trim', explode("\n", $s('cruise_page.booking_ports', "Mumbai\nChennai\nKochi"))));
        $bookingDestinations = array_filter(array_map('trim', explode("\n", $s('cruise_page.booking_destinations', "Goa\nLakshadweep\nSri Lanka"))));

        // ── Cruise record — cabin types from the featured active cruise ───
        $cruise    = Cruise::where('is_active', true)->with(['cabinTypes', 'images'])->first();
        $cabinTypes = $cruise ? $cruise->cabinTypes->map(function ($c) { $c->resolved_image = $c->resolved_image; return $c; }) : collect([]);

        // Hero carousel images from the cruise's images relation
        $heroImages = [];
        if ($cruise && $cruise->images->isNotEmpty()) {
            $heroImages = $cruise->images->sortBy('sort_order')->map(fn($img) => $img->resolved_image)->filter()->values()->toArray();
        }
        // Fallback to hardcoded Unsplash images if none set in DB
        if (empty($heroImages)) {
            $heroImages = [
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80',
                'https://images.unsplash.com/photo-1512100356356-de1b84283e18?auto=format&fit=crop&w=1800&q=80',
                'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1800&q=80',
                'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1800&q=80',
            ];
        }

        return view('pages.cruises', compact(
            'heroEyebrow', 'heroTitle', 'heroSubtitle', 'heroCtaText', 'heroImages',
            'shipStats',
            'destinationsLabel', 'destinationsHeading', 'destinationCards',
            'diningIntro', 'diningItems',
            'entertainmentIntro', 'entertainmentItems',
            'barsIntro', 'barsItems',
            'indulgenceIntro', 'indulgenceItems',
            'eventsItems',
            'trustItems',
            'bookingPorts', 'bookingDestinations',
            'cabinTypes'
        ));
    }

    public function packages()
    {
        $packages = \App\Models\Package::with(['destination', 'images', 'inclusions'])
            ->where('is_active', true)
            ->get();
        
        return view('pages.packages', compact('packages'));
    }

    public function packageDetails($slug)
    {
        $package = \App\Models\Package::with([
            'destination',
            'images',
            'inclusions',
            'exclusions',
            'itineraryDays',
            'highlights',
            'reviews' => fn ($q) => $q->where('is_published', true),
        ])->where('slug', $slug)->firstOrFail();

        return view('pages.package-details', compact('package'));
    }

    public function offers()
    {
        $s = fn (string $key, mixed $default = '') => Setting::get($key, $default);
        $j = fn (string $key, mixed $default = []) => Setting::getJson($key, $default);

        // ── Hero ──────────────────────────────────────────────────────
        $heroEyebrow  = $s('offers_page.hero_eyebrow',  'Limited Time Deals');
        $heroTitle    = $s('offers_page.hero_title',    'Exclusive Deals. <em>Unforgettable</em> Experiences.');
        $heroSubtitle = $s('offers_page.hero_subtitle', 'Handpicked offers on hotels, cruises & flights — updated regularly');

        // Hero carousel images
        $heroImages = array_values(array_filter(array_map(function ($img) {
            if (!empty($img['image_path']) && Storage::disk('public')->exists($img['image_path'])) {
                return Storage::disk('public')->url($img['image_path']);
            }
            return $img['image_url'] ?? null;
        }, $j('offers_page.hero_images', []))));

        if (empty($heroImages)) {
            $heroImages = [
                'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1400&q=85',
                'https://images.unsplash.com/photo-1548574505-5e239809ee19?w=1400&q=85',
                'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1400&q=85',
            ];
        }

        // ── Filter Tabs ───────────────────────────────────────────────
        $filterTabs = $j('offers_page.filter_tabs', [
            ['key' => 'all',       'label' => 'All Offers'],
            ['key' => 'hotels',    'label' => 'Hotels'],
            ['key' => 'cruises',   'label' => 'Cruises'],
            ['key' => 'flights',   'label' => 'Flights'],
            ['key' => 'honeymoon', 'label' => 'Honeymoon'],
            ['key' => 'family',    'label' => 'Family'],
        ]);

        // ── Offer Cards from Database ─────────────────────────────────
        // Pull all active, non-expired offers ordered by sort_order.
        // Group them by category_key so the blade can render each slider.
        $dbOffers = \App\Models\Offer::active()
            ->orderBy('category_key')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        // Build the $categories array in the same shape the blade expects.
        $grouped = $dbOffers->groupBy('category_key');

        $categories = $grouped->map(function ($offers, $catKey) {
            // Use slider_label / slider_title from the first offer in the group
            $first = $offers->first();

            $cards = $offers->map(function ($offer) {
                return [
                    'name'           => $offer->title,
                    'subtitle'       => $offer->subtitle,
                    'price'          => $offer->display_price,
                    'enquire_link'   => $offer->enquire_link,
                    'badge_label'    => $offer->badge_label,
                    'badge_type'     => $offer->badge_type ?? 'badge-gold',
                    'coming_soon'    => (bool) $offer->coming_soon,
                    'resolved_image' => $offer->resolved_image,
                    // Keep raw values for any future use
                    'image_path'     => $offer->image_path,
                    'image_url'      => $offer->image_url,
                ];
            })->values()->toArray();

            return [
                'category_key'  => $catKey,
                'slider_label'  => $first->slider_label,
                'slider_title'  => $first->slider_title,
                'cards'         => $cards,
            ];
        })->values()->toArray();

        // ── Bottom CTA ────────────────────────────────────────────────
        $ctaTag        = $s('offers_page.cta_tag',         'Stay Ahead');
        $ctaHeading    = $s('offers_page.cta_heading',     'Be the First to <em>Know</em>');
        $ctaBody       = $s('offers_page.cta_body',        "Drop your WhatsApp number and we'll notify you the moment a new deal goes live — no spam, ever.");
        $ctaNotifyNote = $s('offers_page.cta_notify_note', "WhatsApp only. We won't call unless you ask.");
        $ctaWhatsapp   = $s('offers_page.cta_whatsapp',    'https://wa.me/9875073788');
        $ctaWaLabel    = $s('offers_page.cta_wa_label',    'Ask for Latest Deals on WhatsApp');

        return view('pages.offers', compact(
            'heroEyebrow', 'heroTitle', 'heroSubtitle', 'heroImages',
            'filterTabs', 'categories',
            'ctaTag', 'ctaHeading', 'ctaBody', 'ctaNotifyNote', 'ctaWhatsapp', 'ctaWaLabel'
        ));
    }

    public function blog()
    {
        $categories = \App\Models\BlogCategory::where('is_active', true)->orderBy('sort_order')->get();
        $trendingPosts = \App\Models\BlogPost::with('category')->where('is_active', true)->where('is_trending', true)->orderBy('sort_order')->get();
        $posts = \App\Models\BlogPost::with('category')->where('is_active', true)->orderBy('sort_order')->get();
        $destinations = \App\Models\FeaturedBlogDestination::orderBy('sort_order')->take(4)->get();

        return view('pages.blog', compact('categories', 'trendingPosts', 'posts', 'destinations'));
    }
    public function downloadItinerary($slug)
    {
        $package = \App\Models\Package::where('slug', $slug)->firstOrFail();

        if (!$package->itinerary_pdf || !Storage::disk('public')->exists($package->itinerary_pdf)) {
            abort(404, 'Itinerary PDF not found.');
        }

        $filename = ($package->slug ?? 'package') . '-itinerary.pdf';
        return Storage::disk('public')->download($package->itinerary_pdf, $filename);
    }

    public function guestDownloadItinerary(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255'
        ]);

        $package = \App\Models\Package::where('slug', $slug)->firstOrFail();

        if (!$package->itinerary_pdf || !Storage::disk('public')->exists($package->itinerary_pdf)) {
            abort(404, 'Itinerary PDF not found.');
        }

        \App\Models\ItineraryDownload::create([
            'package_id'   => $package->id,
            'name'         => $request->name,
            'phone'        => $request->phone,
            'email'        => $request->email,
        ]);

        $filename = ($package->slug ?? 'package') . '-itinerary.pdf';
        return Storage::disk('public')->download($package->itinerary_pdf, $filename);
    }

    public function storeReview(Request $request, $slug)
    {
        $package = \App\Models\Package::where('slug', $slug)->firstOrFail();

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'body'   => 'required|string|max:1000',
            'rating_guide' => 'nullable|integer|min:1|max:5',
            'rating_accommodation' => 'nullable|integer|min:1|max:5',
            'rating_value' => 'nullable|integer|min:1|max:5',
            'rating_itinerary' => 'nullable|integer|min:1|max:5',
            'images.*' => 'nullable|image|max:2048', // up to 2MB per image
        ]);

        $hasBooked = \App\Models\Booking::where('user_id', auth()->id())
            ->where('vertical', 'package')
            ->where('package_id', $package->id)
            ->where('status', 'confirmed')
            ->exists();

        if (!$hasBooked) {
            return back()->with('error', 'You can only review packages you have booked and completed.');
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('reviews', 'public');
            }
        }

        \App\Models\Review::create([
            'user_id'      => auth()->id(),
            'vertical'     => 'package',
            'reference_id' => $package->id,
            'author_name'  => auth()->user()->name,
            'title'        => $request->title,
            'rating'       => $request->rating,
            'rating_guide' => $request->rating_guide,
            'rating_accommodation' => $request->rating_accommodation,
            'rating_value' => $request->rating_value,
            'rating_itinerary' => $request->rating_itinerary,
            'images'       => $imagePaths,
            'body'         => $request->body,
            'is_published' => true,
        ]);

        return back()->with('success', 'Your review has been submitted successfully.');
    }

    public function storeEnquiry(Request $request)
    {
        $request->validate([
            'vertical'     => 'required|string',
            'reference_id' => 'required|integer',
            'name'         => 'required|string|max:255',
            'phone'        => 'required|string|max:20',
            'email'        => 'nullable|email|max:255',
            'checkin'      => 'nullable|string',
            'checkout'     => 'nullable|string',
            'guest_data'   => 'nullable|string',
            'message'      => 'nullable|string|max:1000',
        ]);

        $travelDateFrom = null;
        $travelDateTo = null;
        
        // Basic parsing for dates if they are in 'Y-m-d' format or we can just leave it if they are text
        if (!empty($request->checkin) && strtotime($request->checkin)) {
            $travelDateFrom = date('Y-m-d', strtotime($request->checkin));
        }
        if (!empty($request->checkout) && strtotime($request->checkout)) {
            $travelDateTo = date('Y-m-d', strtotime($request->checkout));
        }

        // Parse pax_adults and pax_children from guest_data JSON if possible
        $paxAdults = 2;
        $paxChildren = 0;
        $guestNotes = [];
        if (!empty($request->guest_data)) {
            $guestData = json_decode($request->guest_data, true);
            if (is_array($guestData)) {
                $paxAdults = array_sum(array_column($guestData, 'adults'));
                $paxChildren = array_reduce($guestData, function($carry, $room) {
                    return $carry + count($room['children'] ?? []);
                }, 0);
            }
        }

        // Build notes field
        $notesStr = null;
        if (!empty($request->message)) {
            $notesStr = trim($request->message);
            if (strlen($notesStr) > 500) $notesStr = substr($notesStr, 0, 497) . '...';
        }

        \App\Models\Enquiry::create([
            'user_id'          => auth()->id(),
            'vertical'         => $request->vertical,
            'reference_id'     => $request->reference_id,
            'name'             => $request->name,
            'phone'            => $request->phone,
            'email'            => $request->email,
            'travel_date_from' => $travelDateFrom,
            'travel_date_to'   => $travelDateTo,
            'pax_adults'       => $paxAdults,
            'pax_children'     => $paxChildren,
            'notes'            => $notesStr,
            'source'           => 'web',
            'status'           => 'new'
        ]);

        return response()->json(['success' => true]);
    }
}
