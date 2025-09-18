<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Agreement;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

echo "=== CHECKING NOTIFICATIONS FOR AGREEMENTS ===\n\n";

try {
    // 1. Buscar acuerdos en estado 'sent'
    $sentAgreements = Agreement::where('status', 'sent')->get();
    
    echo "Found {$sentAgreements->count()} agreements in 'sent' status:\n\n";
    
    foreach ($sentAgreements as $agreement) {
        echo "Agreement ID: {$agreement->id}\n";
        echo "- Client ID: {$agreement->client_id}\n";
        echo "- Provider ID: {$agreement->provider_id}\n";
        echo "- Status: {$agreement->status}\n";
        echo "- Sent at: {$agreement->sent_at}\n";
        echo "- Expires at: {$agreement->expires_at}\n\n";
        
        // Buscar notificaciones para el provider
        $notifications = Notification::where('user_id', $agreement->provider_id)
            ->where('type', 'agreement_sent')
            ->get();
            
        echo "Notifications for provider (ID: {$agreement->provider_id}): {$notifications->count()}\n";
        
        foreach ($notifications as $notification) {
            echo "  - Notification ID: {$notification->id}\n";
            echo "    Type: {$notification->type}\n";
            echo "    Title: {$notification->title}\n";
            echo "    Read: " . ($notification->read_at ? 'YES' : 'NO') . "\n";
            echo "    Created: {$notification->created_at}\n";
            
            // Decodificar data JSON
            $data = json_decode($notification->data, true);
            if ($data && isset($data['agreement_id'])) {
                echo "    Agreement ID in data: {$data['agreement_id']}\n";
                if ($data['agreement_id'] == $agreement->id) {
                    echo "    ✅ MATCHES current agreement!\n";
                } else {
                    echo "    ❌ Different agreement ID\n";
                }
            } else {
                echo "    ❌ No agreement_id in data\n";
            }
            echo "\n";
        }
        
        echo "---\n\n";
    }
    
    // 2. Verificar todas las notificaciones de tipo agreement_sent
    echo "\n=== ALL AGREEMENT_SENT NOTIFICATIONS ===\n\n";
    
    $allAgreementNotifications = Notification::where('type', 'agreement_sent')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
        
    echo "Found {$allAgreementNotifications->count()} agreement_sent notifications (last 5):\n\n";
    
    foreach ($allAgreementNotifications as $notification) {
        echo "Notification ID: {$notification->id}\n";
        echo "- User ID: {$notification->user_id}\n";
        echo "- Type: {$notification->type}\n";
        echo "- Title: {$notification->title}\n";
        echo "- Read: " . ($notification->read_at ? 'YES' : 'NO') . "\n";
        echo "- Created: {$notification->created_at}\n";
        
        $data = json_decode($notification->data, true);
        if ($data) {
            echo "- Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace: {$e->getTraceAsString()}\n";
}

echo "\n=== CHECK COMPLETED ===\n";