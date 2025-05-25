<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class PaymentController extends Controller
{
    public function showPayment($orderId)
    {
        $transaction = Transaction::where('order_id', $orderId)->firstOrFail();

        if (! $transaction->payment_url) {
            return redirect()->back()->with('error', 'Payment link belum dibuat untuk transaksi ini.');
        }

        return view('payment.show', compact('transaction'));
    }

    public function success()
    {
        return view('payment.success');
    }

    public function failed()
    {
        return view('payment.failed');
    }
}
