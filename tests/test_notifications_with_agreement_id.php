<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Agreement;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING NOTIFICATIONS WITH AGREEMENT_ID ===\n\n";

try {
    // 1. Buscar un acuerdo existente en estado 'draft'
    $agreement = Agreement::with(['client', 'provider', 'serviceRequest'])
        ->where('status', Agreement::STATUS_DRAFT)
        ->first();
    
    if (!$agreement) {
        echo "❌ No agreements found in draft status\n";
        exit(1);
    }
    
    echo "Found agreement:\n";
    echo "- Agreement ID: {$agreement->id}\n";
    echo "- Status: {$agreement->status}\n";
    echo "- Client: {$agreement->client->name} (ID: {$agreement->client_id})\n";
    echo "- Provider: {$agreement->provider->name} (ID: {$agreement->provider_id})\n";
    echo "- Service Request: {$agreement->serviceRequest->title}\n\n";
    
    // 2. Simular autenticación como cliente
    Auth::setUser($agreement->client);
    
    echo "Authenticated as client: {$agreement->client->name}\n\n";
    
    // 3. Contar notificaciones antes del envío
    $notificationsBefore = Notification::where('type', 'agreement_sent')->count();
    echo "Notifications before sending: {$notificationsBefore}\n\n";
    
    // 4. Marcar como enviado (esto debería crear las notificaciones)
    echo "Marking agreement as sent...\n";
    $result = $agreement->markAsSent();
    
    if (!$result) {
        echo "❌ Failed to mark agreement as sent\n";
        exit(1);
    }
    
    echo "✅ Agreement marked as sent\n";
    $agreement->refresh();
    echo "- New status: {$agreement->status}\n";
    echo "- Sent at: {$agreement->sent_at}\n\n";
    
    // 5. Verificar las notificaciones creadas
    echo "=== CHECKING NEW NOTIFICATIONS ===\n\n";
    
    $notificationsAfter = Notification::where('type', 'agreement_sent')->count();
    echo "Notifications after sending: {$notificationsAfter}\n";
    echo "New notifications created: " . ($notificationsAfter - $notificationsBefore) . "\n\n";
    
    // Obtener las notificaciones más recientes
    $recentNotifications = Notification::where('type', 'agreement_sent')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
        
    echo "Recent agreement_sent notifications:\n";
    foreach ($recentNotifications as $notification) {
        echo "  - Notification ID: {$notification->id}\n";
        echo "    User ID: {$notification->user_id}\n";
        echo "    Type: {$notification->type}\n";
        echo "    Title: {$notification->title}\n";
        echo "    Created: {$notification->created_at}\n";
        
        $data = json_decode($notification->data, true);
        if ($data) {
            echo "    Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
            if (isset($data['agreement_id'])) {
                echo "    ✅ Agreement ID found: {$data['agreement_id']}\n";
                if ($data['agreement_id'] == $agreement->id) {
                    echo "    ✅ Agreement ID matches current agreement!\n";
                } else {
                    echo "    ⚠️  Agreement ID doesn't match current agreement\n";
                }
            } else {
                echo "    ❌ No agreement_id found in data\n";
            }
        } else {
            echo "    ❌ No data found or invalid JSON\n";
        }
        echo "\n";
    }
    
    // 6. Verificar notificaciones específicas para este acuerdo
    echo "=== CHECKING NOTIFICATIONS FOR THIS AGREEMENT ===\n\n";
    
    $agreementNotifications = Notification::where('type', 'agreement_sent')
        ->whereRaw('JSON_EXTRACT(data, "$.agreement_id") = ?', [$agreement->id])
        ->get();
        
    echo "Notifications for agreement {$agreement->id}: {$agreementNotifications->count()}\n";
    
    foreach ($agreementNotifications as $notification) {
        $user = User::find($notification->user_id);
        echo "  - User: {$user->name} (ID: {$notification->user_id})\n";
        echo "    Role: " . ($notification->user_id == $agreement->client_id ? 'Client' : 'Provider') . "\n";
        echo "    Title: {$notification->title}\n";
        echo "    Created: {$notification->created_at}\n\n";
    }
    
    echo "✅ Test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace: {$e->getTraceAsString()}\n";
    exit(1);
}