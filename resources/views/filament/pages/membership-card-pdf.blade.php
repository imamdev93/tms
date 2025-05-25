<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .card {
            width: 80mm;
            height: 50mm;
            border: 1px solid #ddd;
            padding: 5mm;
            position: relative;
        }
        .header { 
            text-align: center; 
            margin-bottom: 3mm; 
            padding-bottom: 2mm;
            border-bottom: 1px solid #eee;
        }
        .member-info { margin-bottom: 2mm; font-size: 10pt; }
        .card-number { font-weight: bold; margin: 2mm 0; text-align: center; }
        .dates { font-size: 8pt; color: #555; display: flex; justify-content: space-between; }
        .qr-code { position: absolute; right: 5mm; bottom: 5mm; width: 20mm; height: 20mm; }
        .status-badge {
            position: absolute;
            top: 5mm;
            right: 5mm;
            padding: 2mm 3mm;
            border-radius: 9999px;
            font-size: 8pt;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h3>MEMBERSHIP CARD</h3>
        </div>
        
        <div class="status-badge {{ match($card->status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-yellow-100 text-yellow-800',
            'expired' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        } }}">
            {{ strtoupper($card->status) }}
        </div>
        
        <div class="member-info">
            <strong>Name:</strong> {{ $card->member->name }}<br>
            <strong>Member ID:</strong> {{ $card->member->id }}
        </div>
        
        <div class="card-number">
            {{ $card->card_number }}
        </div>
        
        <div class="dates">
            <span><strong>Issued:</strong> {{ $card->issue_date->format('d/m/Y') }}</span>
            <span><strong>Expires:</strong> {{ $card->expiry_date->format('d/m/Y') }}</span>
        </div>
        
        <div class="qr-code">
        </div>
    </div>
</body>
</html>