<?php

namespace App\Payments;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function createCheckout(Order $order): PaymentCheckout;

    public function verifyCallback(Request $request): VerifiedPayment;
}
