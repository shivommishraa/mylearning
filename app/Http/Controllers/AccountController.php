<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('account.dashboard', [
            'orders' => auth()->user()->orders()->latest()->take(5)->get(),
            'addresses' => auth()->user()->addresses()->count(),
            'orderCount' => auth()->user()->orders()->count(),
        ]);
    }

    public function wishlist(): View
    {
        return view('account.wishlist', [
            'products' => auth()->user()->wishlist()->where('is_active', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->with(['category', 'brand'])->paginate(12),
        ]);
    }

    public function orders(Request $request): View
    {
        return view('account.orders', [
            'orders' => $request->user()->orders()->latest()->paginate(15),
        ]);
    }
}
