<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('admin.customers.index', [
            'customers' => User::whereDoesntHave('roles')->withCount('orders')->latest()->paginate(20),
        ]);
    }

    public function enquiries(): View
    {
        return view('admin.enquiries.index', ['enquiries' => Enquiry::latest()->paginate(20)]);
    }

    public function markEnquiryRead(Enquiry $enquiry)
    {
        $enquiry->update(['read_at' => now()]);

        return back()->with('success', 'Enquiry marked as read.');
    }

    public function activity(): View
    {
        return view('admin.activity.index', ['logs' => ActivityLog::with('user')->latest()->paginate(25)]);
    }
}
