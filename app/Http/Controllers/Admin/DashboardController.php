<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $sales = Order::where('payment_status', '!=', 'failed')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');
        $chart = collect(range(6, 0))->map(function ($daysAgo) use ($sales) {
            $day = now()->subDays($daysAgo)->toDateString();

            return ['day' => $day, 'label' => now()->subDays($daysAgo)->format('D'), 'total' => (float) ($sales[$day]->total ?? 0)];
        });

        return view('admin.dashboard', [
            'orders' => Order::count(),
            'pending' => Order::whereIn('status', ['placed', 'confirmed', 'packing'])->count(),
            'revenue' => (float) Order::whereIn('status', ['confirmed', 'packing', 'shipped', 'delivered'])->sum('total'),
            'customers' => User::whereDoesntHave('roles')->count(),
            'lowStock' => Product::where('is_active', true)->where('stock', '<=', 10)->orderBy('stock')->take(6)->get(),
            'recentOrders' => Order::with('user')->latest()->take(6)->get(),
            'chart' => $chart,
        ]);
    }
}
