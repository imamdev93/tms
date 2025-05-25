<?php

namespace App\Http\Controllers;

use App\FraudStatusEnum;
use App\Models\Transaction;
use App\PaymentStatusEnum;
use App\StatusEnum;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function handleNotification()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);

        try {
            $notification = new Notification;

            $orderId = $notification->order_id;
            $statusCode = $notification->status_code;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;
            $paymentType = $notification->payment_type;

            $transaction = Transaction::where('order_id', $orderId)->first();

            if (! $transaction) {
                return response()->json(['message' => 'Transaksi tidak ditemukan'], Response::HTTP_NOT_FOUND);
            }

            // Update transaksi berdasarkan status dari Midtrans
            $status = $this->getStatus($transactionStatus, $fraudStatus);
            $paymentUrl = $status != PaymentStatusEnum::COMPLETED->value ? $transaction->payment_url : null;
            $orderId = $status != PaymentStatusEnum::COMPLETED->value ? $transaction->order_id : null;

            $transaction->update([
                'payment_url' => $paymentUrl,
                'order_id' => $orderId,
                'status' => $status,
                'payment_type' => $paymentType,
                'metadata' => [
                    'notification' => $notification,
                    'status_code' => $statusCode,
                    'transaction_status' => $status,
                    'fraud_status' => $fraudStatus,
                ],
            ]);

            // status completed
            if ($status === PaymentStatusEnum::COMPLETED->value) {
                $now = new Carbon();

                $payloadUpdateMember = [
                    'status' => StatusEnum::ACTIVE->value,
                    'start_date' => $now->format('Y-m-d'),
                    'end_date' => $now->addYear()->format('Y-m-d'),
                ];

                if ($transaction->user?->member) {
                    $transaction->user?->member?->update($payloadUpdateMember);
                }
            }

            return response()->json(['message' => 'Notification processed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getStatus($transaction, $fraudStatus)
    {
        return match ($transaction) {
            PaymentStatusEnum::CAPTURE->value => match ($fraudStatus) {
                FraudStatusEnum::CHALLENGE->value => PaymentStatusEnum::PROCESSING->value,
                FraudStatusEnum::ACCEPT->value => PaymentStatusEnum::COMPLETED->value,
                default => PaymentStatusEnum::PENDING->value,
            },
            PaymentStatusEnum::SETTLEMENT->value => PaymentStatusEnum::COMPLETED->value,
            PaymentStatusEnum::CANCEL->value,
            PaymentStatusEnum::DENY->value,
            PaymentStatusEnum::EXPIRE->value => PaymentStatusEnum::FAILED->value,
            PaymentStatusEnum::PENDING->value => PaymentStatusEnum::PROCESSING->value,
            default => PaymentStatusEnum::PENDING->value,
        };
    }
}
