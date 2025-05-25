{{-- @extends('layouts.app')

@section('content') --}}
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Detail Pembayaran</div>

                <div class="card-body">
                    <div class="mb-3">
                        <p><strong>Order ID:</strong> {{ $transaction->order_id }}</p>
                        <p><strong>Jumlah:</strong> Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                        <p><strong>Status:</strong> {{ ucfirst($transaction->status) }}</p>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ $transaction->payment_url }}" class="btn btn-primary" target="_blank">Bayar Sekarang</a>
                    </div>
                    
                    <div class="mt-3">
                        <small>Catatan: Setelah pembayaran selesai, Anda akan diarahkan kembali ke situs kami.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- @endsection --}}