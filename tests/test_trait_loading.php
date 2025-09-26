<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Constants\NotificationType;

// Test if the trait is loaded correctly
$user = User::first();

echo "=== TESTING TRAIT LOADING ===\n";
echo "User class: " . get_class($user) . "\n";
echo "Available methods:\n";

$methods = get_class_methods($user);
sort($methods);

foreach ($methods as $method) {
    if (strpos($method, 'notification') !== false || strpos($method, 'Notification') !== false) {
        echo "  - $method\n";
    }
}

echo "\n=== CHECKING TRAITS ===\n";
$traits = class_uses_recursive(User::class);
foreach ($traits as $trait) {
    echo "  - $trait\n";
}

echo "\n=== TESTING METHOD EXISTENCE ===\n";
echo "method_exists(createNotification): " . (method_exists($user, 'createNotification') ? 'YES' : 'NO') . "\n";
echo "is_callable(createNotification): " . (is_callable([$user, 'createNotification']) ? 'YES' : 'NO') . "\n";

echo "\n=== TESTING REFLECTION ===\n";
try {
    $reflection = new ReflectionClass($user);
    $method = $reflection->getMethod('createNotification');
    echo "Method found via reflection: YES\n";
    echo "Method is public: " . ($method->isPublic() ? 'YES' : 'NO') . "\n";
    echo "Method is protected: " . ($method->isProtected() ? 'YES' : 'NO') . "\n";
} catch (ReflectionException $e) {
    echo "Method not found via reflection: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";