<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Cruise;
use App\Models\Staycation;
use App\Models\Offer;

class FrontendController extends Controller
{
    public function index()
    {
        return view('pages.home');
    }

    public function hotels()
    {
        $hotels = Hotel::with(['destination', 'amenities', 'images'])->where('is_active', true)->get();
        return view('pages.hotels', compact('hotels'));
    }

    public function cruises()
    {
        // $cruises = Cruise::with(['destination', 'images'])->where('is_active', true)->get();
        return view('pages.cruises');
    }

    public function staycations()
    {
        // $staycations = Staycation::with(['destination', 'amenities', 'images'])->where('is_active', true)->get();
        return view('pages.staycation');
    }

    public function offers()
    {
        $offers = Offer::where('is_active', true)->get();
        return view('pages.offers', compact('offers'));
    }
}
