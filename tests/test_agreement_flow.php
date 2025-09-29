<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ServiceRequest;
use App\Models\ServiceOffer;
use App\Models\Agreement;
use App\Http\Resources\User\AgreementResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=== TESTING AGREEMENT FLOW ===\n\n";

try {
    // 1. Buscar un acuerdo existente en estado 'sent'
    $sentAgreement = Agreement::where('status', 'sent')
        ->with(['serviceRequest', 'serviceOffer', 'client', 'provider'])
        ->first();
    
    if ($sentAgreement) {
        echo "Found existing agreement in 'sent' status:\n";
        echo "- Agreement ID: {$sentAgreement->id}\n";
        echo "- Client ID: {$sentAgreement->client_id}\n";
        echo "- Provider ID: {$sentAgreement->provider_id}\n";
        echo "- Status: {$sentAgreement->status}\n";
        echo "- Service Request: {$sentAgreement->serviceRequest->title}\n\n";
        
        // Simular autenticación como provider
        Auth::setUser($sentAgreement->provider);
        echo "Authenticated as provider (ID: {$sentAgreement->provider_id})\n";
        
        // Crear resource para verificar permisos
        $resource = new AgreementResource($sentAgreement);
        $resourceArray = $resource->toArray(request());
        
        echo "\nPermissions for provider:\n";
        foreach ($resourceArray['permissions'] as $permission => $value) {
            echo "- {$permission}: " . ($value ? 'YES' : 'NO') . "\n";
        }
        
        echo "\nFlags:\n";
        foreach ($resourceArray['flags'] as $flag => $value) {
            echo "- {$flag}: " . ($value ? 'YES' : 'NO') . "\n";
        }
        
        // Ahora simular autenticación como client
        Auth::setUser($sentAgreement->client);
        echo "\n\nAuthenticated as client (ID: {$sentAgreement->client_id})\n";
        
        $resource = new AgreementResource($sentAgreement);
        $resourceArray = $resource->toArray(request());
        
        echo "\nPermissions for client:\n";
        foreach ($resourceArray['permissions'] as $permission => $value) {
            echo "- {$permission}: " . ($value ? 'YES' : 'NO') . "\n";
        }
        
    } else {
        echo "No agreements found in 'sent' status. Let's create one...\n\n";
        
        // Buscar una oferta pendiente
        $pendingOffer = ServiceOffer::where('status', 'pending')
            ->with(['serviceRequest', 'user'])
            ->first();
            
        if (!$pendingOffer) {
            echo "No pending offers found. Cannot create agreement.\n";
            exit(1);
        }
        
        echo "Found pending offer:\n";
        echo "- Offer ID: {$pendingOffer->id}\n";
        echo "- Service Request: {$pendingOffer->serviceRequest->title}\n";
        echo "- Client ID: {$pendingOffer->serviceRequest->user_id}\n";
        echo "- Provider ID: {$pendingOffer->user_id}\n\n";
        
        // Autenticar como cliente
        Auth::setUser($pendingOffer->serviceRequest->user);
        
        DB::beginTransaction();
        
        // Crear acuerdo
        $agreement = Agreement::create([
            'service_request_id' => $pendingOffer->service_request_id,
            'service_offer_id' => $pendingOffer->id,
            'client_id' => $pendingOffer->serviceRequest->user_id,
            'provider_id' => $pendingOffer->user_id,
            'status' => Agreement::STATUS_DRAFT,
            'terms' => [
                'price' => $pendingOffer->price_proposed,
                'estimated_time' => $pendingOffer->estimated_time,
                'description' => $pendingOffer->message,
                'service_title' => $pendingOffer->serviceRequest->title,
                'created_at' => now()->toISOString()
            ],
            'version' => 1,
        ]);
        
        echo "Agreement created with ID: {$agreement->id}\n";
        
        // Enviar el acuerdo
        $sent = $agreement->markAsSent(now()->addDays(7));
        
        if ($sent) {
            echo "Agreement sent successfully!\n";
            echo "- Status: {$agreement->fresh()->status}\n";
            echo "- Sent at: {$agreement->fresh()->sent_at}\n";
            echo "- Expires at: {$agreement->fresh()->expires_at}\n\n";
            
            // Verificar permisos como provider
            Auth::setUser($agreement->provider);
            echo "Authenticated as provider (ID: {$agreement->provider_id})\n";
            
            $resource = new AgreementResource($agreement->fresh());
            $resourceArray = $resource->toArray(request());
            
            echo "\nPermissions for provider:\n";
            foreach ($resourceArray['permissions'] as $permission => $value) {
                echo "- {$permission}: " . ($value ? 'YES' : 'NO') . "\n";
            }
            
            // Verificar que puede aceptar y rechazar
            if ($resourceArray['permissions']['can_accept'] && $resourceArray['permissions']['can_reject']) {
                echo "\n✅ SUCCESS: Provider can accept and reject the agreement!\n";
            } else {
                echo "\n❌ ERROR: Provider cannot accept/reject the agreement!\n";
                echo "- can_accept: " . ($resourceArray['permissions']['can_accept'] ? 'YES' : 'NO') . "\n";
                echo "- can_reject: " . ($resourceArray['permissions']['can_reject'] ? 'YES' : 'NO') . "\n";
            }
            
        } else {
            echo "❌ ERROR: Failed to send agreement!\n";
        }
        
        DB::commit();
    }
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace: {$e->getTraceAsString()}\n";
}

echo "\n=== TEST COMPLETED ===\n";