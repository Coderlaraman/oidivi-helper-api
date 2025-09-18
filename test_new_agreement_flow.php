<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Agreement;
use App\Models\ServiceRequest;
use App\Models\ServiceOffer;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING NEW AGREEMENT FLOW ===\n\n";

try {
    // 1. Buscar una oferta de servicio que no tenga acuerdo
    $serviceOffer = ServiceOffer::with(['serviceRequest', 'user'])
        ->where('status', 'pending')
        ->whereDoesntHave('agreement')
        ->first();
    
    if (!$serviceOffer) {
        echo "❌ No service offers found in pending status\n";
        exit(1);
    }
    
    echo "Found service offer:\n";
    echo "- Offer ID: {$serviceOffer->id}\n";
    echo "- Service Request: {$serviceOffer->serviceRequest->title}\n";
    echo "- Provider: {$serviceOffer->user->name} (ID: {$serviceOffer->user_id})\n";
    echo "- Client: {$serviceOffer->serviceRequest->user->name} (ID: {$serviceOffer->serviceRequest->user_id})\n\n";
    
    // 2. Simular autenticación como cliente
    $client = $serviceOffer->serviceRequest->user;
    Auth::setUser($client);
    
    echo "Authenticated as client: {$client->name} (ID: {$client->id})\n\n";
    
    // 3. Crear un nuevo acuerdo
    DB::beginTransaction();
    
    $agreement = Agreement::create([
        'service_request_id' => $serviceOffer->service_request_id,
        'service_offer_id' => $serviceOffer->id,
        'client_id' => $client->id,
        'provider_id' => $serviceOffer->user_id,
        'status' => Agreement::STATUS_DRAFT,
        'terms' => json_encode([
            'description' => 'Test agreement terms',
            'price' => 100.00,
            'currency' => 'USD',
            'timeline' => '7 days'
        ]),
        'expires_at' => now()->addDays(7),
        'version' => 1,
    ]);
    
    echo "✅ Agreement created:\n";
    echo "- Agreement ID: {$agreement->id}\n";
    echo "- Status: {$agreement->status}\n";
    echo "- Client ID: {$agreement->client_id}\n";
    echo "- Provider ID: {$agreement->provider_id}\n";
    echo "- Expires at: {$agreement->expires_at}\n\n";
    
    // 4. Marcar como enviado (esto debería crear las notificaciones)
    echo "Marking agreement as sent...\n";
    $agreement->markAsSent();
    
    echo "✅ Agreement marked as sent\n";
    echo "- New status: {$agreement->fresh()->status}\n";
    echo "- Sent at: {$agreement->fresh()->sent_at}\n\n";
    
    // 5. Verificar las notificaciones creadas
    echo "=== CHECKING NOTIFICATIONS ===\n\n";
    
    // Notificaciones para el provider (helper)
    $providerNotifications = Notification::where('user_id', $agreement->provider_id)
        ->where('type', 'agreement_sent')
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
        
    echo "Provider notifications: {$providerNotifications->count()}\n";
    foreach ($providerNotifications as $notification) {
        echo "  - Notification ID: {$notification->id}\n";
        echo "    Type: {$notification->type}\n";
        echo "    Title: {$notification->title}\n";
        echo "    Created: {$notification->created_at}\n";
        
        $data = json_decode($notification->data, true);
        if ($data) {
            echo "    Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
            if (isset($data['agreement_id']) && $data['agreement_id'] == $agreement->id) {
                echo "    ✅ Agreement ID matches!\n";
            } else {
                echo "    ❌ Agreement ID mismatch or missing\n";
            }
        } else {
            echo "    ❌ No data found\n";
        }
        echo "\n";
    }
    
    // Notificaciones para el client
    $clientNotifications = Notification::where('user_id', $agreement->client_id)
        ->where('type', 'agreement_sent')
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
        
    echo "Client notifications: {$clientNotifications->count()}\n";
    foreach ($clientNotifications as $notification) {
        echo "  - Notification ID: {$notification->id}\n";
        echo "    Type: {$notification->type}\n";
        echo "    Title: {$notification->title}\n";
        echo "    Created: {$notification->created_at}\n";
        
        $data = json_decode($notification->data, true);
        if ($data) {
            echo "    Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
            if (isset($data['agreement_id']) && $data['agreement_id'] == $agreement->id) {
                echo "    ✅ Agreement ID matches!\n";
            } else {
                echo "    ❌ Agreement ID mismatch or missing\n";
            }
        } else {
            echo "    ❌ No data found\n";
        }
        echo "\n";
    }
    
    DB::commit();
    
    echo "\n=== TEST COMPLETED SUCCESSFULLY ===\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace: {$e->getTraceAsString()}\n";
}