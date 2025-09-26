<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Agreement;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

echo "=== VERIFYING NOTIFICATION IMPROVEMENTS ===\n\n";

try {
    // 1. Verificar todas las notificaciones de tipo agreement_sent
    $agreementNotifications = Notification::where('type', 'agreement_sent')
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Total agreement_sent notifications: {$agreementNotifications->count()}\n\n";
    
    if ($agreementNotifications->count() === 0) {
        echo "❌ No agreement_sent notifications found\n";
        exit(1);
    }
    
    // 2. Analizar cada notificación
    $withAgreementId = 0;
    $withoutAgreementId = 0;
    
    echo "=== NOTIFICATION ANALYSIS ===\n\n";
    
    foreach ($agreementNotifications as $notification) {
        $user = User::find($notification->user_id);
        echo "Notification ID: {$notification->id}\n";
        echo "  User: {$user->name} (ID: {$notification->user_id})\n";
        echo "  Title: {$notification->title}\n";
        echo "  Created: {$notification->created_at}\n";
        
        $data = is_array($notification->data) ? $notification->data
            : (is_string($notification->data) && $notification->data !== '' ? json_decode($notification->data, true) : null);
        if ($data && isset($data['agreement_id'])) {
            echo "  ✅ Agreement ID: {$data['agreement_id']}\n";
            echo "  Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
            $withAgreementId++;
            
            // Verificar si el acuerdo existe
            $agreement = Agreement::find($data['agreement_id']);
            if ($agreement) {
                echo "  ✅ Agreement exists: {$agreement->status}\n";
                echo "  Client: {$agreement->client->name} (ID: {$agreement->client_id})\n";
                echo "  Provider: {$agreement->provider->name} (ID: {$agreement->provider_id})\n";
            } else {
                echo "  ❌ Agreement not found\n";
            }
        } else {
            echo "  ❌ No agreement_id in data\n";
            if ($data) {
                echo "  Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
            } else {
                echo "  Raw data: " . (is_string($notification->data) ? $notification->data : json_encode($notification->data)) . "\n";
            }
            $withoutAgreementId++;
        }
        echo "\n";
    }
    
    // 3. Resumen
    echo "=== SUMMARY ===\n\n";
    echo "Total notifications: {$agreementNotifications->count()}\n";
    echo "With agreement_id: {$withAgreementId}\n";
    echo "Without agreement_id: {$withoutAgreementId}\n";
    
    if ($withAgreementId > 0) {
        echo "\n✅ SUCCESS: Found notifications with agreement_id!\n";
        echo "The notification system has been successfully improved.\n";
    } else {
        echo "\n❌ No notifications found with agreement_id\n";
        echo "The notification system still needs to be tested with new agreements.\n";
    }
    
    // 4. Verificar el método createNotification actualizado
    echo "\n=== CHECKING CREATENOTIFICATION METHOD ===\n\n";
    
    // Buscar cualquier acuerdo para probar el método
    $testAgreement = Agreement::with(['client', 'provider'])->first();
    
    if ($testAgreement) {
        echo "Found test agreement: {$testAgreement->id}\n";
        echo "Client: {$testAgreement->client->name}\n";
        echo "Provider: {$testAgreement->provider->name}\n\n";
        
        // Probar el método createNotification con data
        echo "Testing createNotification method with agreement data...\n";
        
        $testData = [
            'agreement_id' => $testAgreement->id,
            'status' => $testAgreement->status,
            'test' => true
        ];
        
        try {
            $testNotifications = $testAgreement->client->createNotification(
                [$testAgreement->client->id], // Array of user IDs
                'agreement_sent',
                'Test Agreement Sent',
                'This is a test notification to verify the createNotification method',
                $testData
            );
            
            echo "✅ Test notifications created: " . count($testNotifications) . "\n";
            
            foreach ($testNotifications as $testNotification) {
                echo "✅ Test notification created: {$testNotification->id}\n";
                
                $testNotificationData = is_array($testNotification->data) ? $testNotification->data
                    : (is_string($testNotification->data) && $testNotification->data !== '' ? json_decode($testNotification->data, true) : null);
                if ($testNotificationData && isset($testNotificationData['agreement_id'])) {
                    echo "✅ Test notification contains agreement_id: {$testNotificationData['agreement_id']}\n";
                    echo "✅ createNotification method is working correctly!\n";
                    
                    // Limpiar la notificación de prueba
                    $testNotification->delete();
                    echo "Test notification cleaned up.\n";
                } else {
                    echo "❌ Test notification doesn't contain agreement_id\n";
                }
            }
        } catch (Exception $e) {
            echo "❌ Error testing createNotification: {$e->getMessage()}\n";
        }
    } else {
        echo "No agreements found for testing\n";
    }
    
    echo "\n✅ Verification completed!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace: {$e->getTraceAsString()}\n";
    exit(1);
}