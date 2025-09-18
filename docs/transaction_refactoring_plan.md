# Plan de Refactorización: Transacciones como Entidad Central

## Análisis de la Situación Actual

### Arquitectura Actual
Actualmente, el sistema tiene una arquitectura donde:

1. **Payment** es la entidad principal para movimientos financieros
2. **Transaction** existe pero actúa como un registro secundario vinculado a Payment
3. La lógica de negocio está distribuida entre Payment y Agreement
4. Los estados y tipos están duplicados entre Payment y Transaction

### Problemas Identificados

1. **Duplicación de Lógica**: Payment y Transaction manejan estados similares
2. **Acoplamiento Fuerte**: Payment está fuertemente acoplado a Stripe y Agreement
3. **Limitaciones de Escalabilidad**: Difícil agregar nuevos tipos de movimientos financieros
4. **Inconsistencia de Datos**: Dos fuentes de verdad para información financiera
5. **Complejidad de Auditoría**: Seguimiento fragmentado de movimientos financieros

## Propuesta de Nueva Arquitectura

### Principios de Diseño

1. **Transaction como Entidad Central**: Todas las operaciones financieras son transacciones
2. **Payment como Implementación**: Payment se convierte en un tipo específico de transacción
3. **Separación de Responsabilidades**: Lógica de negocio separada de implementación de pagos
4. **Extensibilidad**: Fácil agregar nuevos tipos de transacciones
5. **Auditoría Completa**: Historial completo de todos los movimientos financieros

### Estructura Propuesta

```
Transaction (Entidad Central)
├── PaymentTransaction (Pagos de clientes)
├── RefundTransaction (Reembolsos)
├── CommissionTransaction (Comisiones de plataforma)
├── WithdrawalTransaction (Retiros de helpers)
├── BonusTransaction (Bonificaciones)
└── FeeTransaction (Tarifas adicionales)
```

## Plan de Implementación

### Fase 1: Preparación y Diseño

#### 1.1 Actualizar Modelo Transaction

**Campos a agregar/modificar:**
```php
// Campos existentes mejorados
'type' => 'enum' // payment, refund, commission, withdrawal, bonus, fee
'status' => 'enum' // pending, processing, completed, failed, cancelled
'amount' => 'decimal(15,2)' // Aumentar precisión
'currency' => 'string(3)'

// Nuevos campos
'transaction_group_id' => 'uuid' // Agrupar transacciones relacionadas
'parent_transaction_id' => 'bigint' // Para transacciones derivadas
'payment_method' => 'string' // stripe, paypal, bank_transfer
'payment_provider_id' => 'string' // ID del proveedor de pago
'payment_provider_data' => 'json' // Metadatos del proveedor
'fee_amount' => 'decimal(10,2)' // Comisiones aplicadas
'net_amount' => 'decimal(15,2)' // Monto neto después de comisiones
'exchange_rate' => 'decimal(10,6)' // Para conversiones de moneda
'original_amount' => 'decimal(15,2)' // Monto original antes de conversión
'original_currency' => 'string(3)' // Moneda original
'settlement_date' => 'timestamp' // Fecha de liquidación
'reconciliation_status' => 'enum' // pending, reconciled, disputed
'risk_score' => 'integer' // Puntuación de riesgo
'compliance_status' => 'enum' // approved, under_review, rejected
```

**Nuevos tipos de transacción:**
```php
const TYPE_PAYMENT = 'payment';           // Pago de cliente a helper
const TYPE_REFUND = 'refund';             // Reembolso de helper a cliente
const TYPE_COMMISSION = 'commission';     // Comisión de plataforma
const TYPE_WITHDRAWAL = 'withdrawal';     // Retiro de helper
const TYPE_DEPOSIT = 'deposit';           // Depósito a helper
const TYPE_BONUS = 'bonus';               // Bonificación
const TYPE_FEE = 'fee';                   // Tarifa adicional
const TYPE_CHARGEBACK = 'chargeback';     // Contracargo
const TYPE_ADJUSTMENT = 'adjustment';     // Ajuste manual
const TYPE_ESCROW_HOLD = 'escrow_hold';   // Retención en escrow
const TYPE_ESCROW_RELEASE = 'escrow_release'; // Liberación de escrow
```

**Nuevos estados:**
```php
const STATUS_PENDING = 'pending';         // Pendiente de procesamiento
const STATUS_PROCESSING = 'processing';   // En procesamiento
const STATUS_COMPLETED = 'completed';     // Completada exitosamente
const STATUS_FAILED = 'failed';           // Falló el procesamiento
const STATUS_CANCELLED = 'cancelled';     // Cancelada
const STATUS_DISPUTED = 'disputed';       // En disputa
const STATUS_REFUNDED = 'refunded';       // Reembolsada
const STATUS_PARTIALLY_REFUNDED = 'partially_refunded'; // Parcialmente reembolsada
const STATUS_HELD = 'held';               // Retenida (escrow)
const STATUS_RELEASED = 'released';       // Liberada de retención
```

#### 1.2 Crear Servicios de Negocio

**TransactionService:**
```php
class TransactionService
{
    public function createPaymentTransaction(Agreement $agreement, array $paymentData): Transaction
    public function createRefundTransaction(Transaction $originalTransaction, float $amount): Transaction
    public function createCommissionTransaction(Transaction $paymentTransaction): Transaction
    public function processTransaction(Transaction $transaction): bool
    public function cancelTransaction(Transaction $transaction, string $reason): bool
    public function getTransactionHistory(User $user, array $filters = []): Collection
    public function calculateBalance(User $user): array
    public function reconcileTransaction(Transaction $transaction): bool
}
```

**PaymentProcessorService:**
```php
class PaymentProcessorService
{
    public function processStripePayment(Transaction $transaction): PaymentResult
    public function processRefund(Transaction $transaction): RefundResult
    public function handleWebhook(string $provider, array $payload): void
    public function validatePaymentMethod(string $method, array $data): bool
}
```

### Fase 2: Migración de Datos

#### 2.1 Migración de Pagos Existentes

```php
// Migración para convertir pagos existentes en transacciones
class MigratePaymentsToTransactions extends Migration
{
    public function up()
    {
        // 1. Crear transacciones para pagos existentes
        $payments = Payment::all();
        
        foreach ($payments as $payment) {
            // Crear transacción de pago (cliente -> helper)
            $paymentTransaction = Transaction::create([
                'user_id' => $payment->payer_user_id,
                'related_model_type' => Agreement::class,
                'related_model_id' => $payment->agreement_id,
                'type' => Transaction::TYPE_PAYMENT,
                'amount' => -$payment->amount, // Negativo para el pagador
                'currency' => $payment->currency,
                'status' => $this->mapPaymentStatus($payment->status),
                'payment_method' => 'stripe',
                'payment_provider_id' => $payment->stripe_payment_intent_id,
                'payment_provider_data' => $payment->stripe_metadata,
                'processed_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
            ]);
            
            // Crear transacción de depósito (helper recibe)
            Transaction::create([
                'user_id' => $payment->payee_user_id,
                'related_model_type' => Agreement::class,
                'related_model_id' => $payment->agreement_id,
                'type' => Transaction::TYPE_DEPOSIT,
                'amount' => $payment->amount, // Positivo para el receptor
                'currency' => $payment->currency,
                'status' => $this->mapPaymentStatus($payment->status),
                'parent_transaction_id' => $paymentTransaction->id,
                'processed_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
            ]);
            
            // Crear transacción de comisión si aplica
            $commissionAmount = $payment->amount * 0.05; // 5% comisión
            if ($commissionAmount > 0) {
                Transaction::create([
                    'user_id' => null, // Plataforma
                    'related_model_type' => Agreement::class,
                    'related_model_id' => $payment->agreement_id,
                    'type' => Transaction::TYPE_COMMISSION,
                    'amount' => $commissionAmount,
                    'currency' => $payment->currency,
                    'status' => Transaction::STATUS_COMPLETED,
                    'parent_transaction_id' => $paymentTransaction->id,
                    'processed_at' => $payment->paid_at,
                    'created_at' => $payment->created_at,
                ]);
            }
        }
    }
}
```

### Fase 3: Refactorización de Modelos

#### 3.1 Actualizar Modelo Payment

```php
class Payment extends Model
{
    // Payment se convierte en un wrapper/facade para transacciones de pago
    
    public function getMainTransaction(): Transaction
    {
        return $this->transactions()
            ->where('type', Transaction::TYPE_PAYMENT)
            ->first();
    }
    
    public function getDepositTransaction(): Transaction
    {
        return $this->transactions()
            ->where('type', Transaction::TYPE_DEPOSIT)
            ->first();
    }
    
    public function getCommissionTransaction(): ?Transaction
    {
        return $this->transactions()
            ->where('type', Transaction::TYPE_COMMISSION)
            ->first();
    }
    
    // Métodos de compatibilidad
    public function getStatusAttribute(): string
    {
        return $this->getMainTransaction()->status;
    }
    
    public function getAmountAttribute(): float
    {
        return abs($this->getMainTransaction()->amount);
    }
}
```

#### 3.2 Actualizar Modelo Agreement

```php
class Agreement extends Model
{
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'related_model_id')
            ->where('related_model_type', self::class);
    }
    
    public function paymentTransactions(): HasMany
    {
        return $this->transactions()
            ->whereIn('type', [Transaction::TYPE_PAYMENT, Transaction::TYPE_DEPOSIT]);
    }
    
    public function getPaymentStatus(): string
    {
        $paymentTransaction = $this->transactions()
            ->where('type', Transaction::TYPE_PAYMENT)
            ->first();
            
        return $paymentTransaction ? $paymentTransaction->status : 'unpaid';
    }
    
    public function getTotalPaid(): float
    {
        return $this->transactions()
            ->where('type', Transaction::TYPE_PAYMENT)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('amount');
    }
}
```

### Fase 4: Actualización de Controladores

#### 4.1 Nuevo TransactionController

```php
class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private PaymentProcessorService $paymentProcessor
    ) {}
    
    public function index(Request $request)
    {
        $transactions = $this->transactionService->getTransactionHistory(
            $request->user(),
            $request->only(['type', 'status', 'date_from', 'date_to'])
        );
        
        return UserTransactionResource::collection($transactions);
    }
    
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return new UserTransactionResource($transaction);
    }
    
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'agreement_id' => 'required|exists:agreements,id',
            'payment_method' => 'required|string',
            'return_url' => 'required|url',
        ]);
        
        $agreement = Agreement::findOrFail($validated['agreement_id']);
        $this->authorize('pay', $agreement);
        
        $transaction = $this->transactionService->createPaymentTransaction(
            $agreement,
            $validated
        );
        
        $result = $this->paymentProcessor->processStripePayment($transaction);
        
        return response()->json([
            'transaction_id' => $transaction->id,
            'payment_url' => $result->paymentUrl,
            'status' => $transaction->status,
        ]);
    }
}
```

### Fase 5: Actualización del Frontend

#### 5.1 Nuevos Tipos TypeScript

```typescript
// types/transaction.ts
export interface Transaction {
  id: string;
  user_id: string;
  type: TransactionType;
  status: TransactionStatus;
  amount: number;
  currency: string;
  description: string;
  payment_method?: string;
  payment_provider_id?: string;
  fee_amount?: number;
  net_amount: number;
  processed_at?: string;
  created_at: string;
  updated_at: string;
  related_model?: {
    type: string;
    id: string;
    data?: any;
  };
  parent_transaction?: Transaction;
  child_transactions?: Transaction[];
}

export type TransactionType = 
  | 'payment'
  | 'refund'
  | 'commission'
  | 'withdrawal'
  | 'deposit'
  | 'bonus'
  | 'fee'
  | 'chargeback'
  | 'adjustment'
  | 'escrow_hold'
  | 'escrow_release';

export type TransactionStatus =
  | 'pending'
  | 'processing'
  | 'completed'
  | 'failed'
  | 'cancelled'
  | 'disputed'
  | 'refunded'
  | 'partially_refunded'
  | 'held'
  | 'released';
```

#### 5.2 Actualizar Componentes

```typescript
// components/TransactionList.tsx
interface TransactionListProps {
  transactions: Transaction[];
  showGrouped?: boolean;
  filterByType?: TransactionType[];
  onTransactionClick?: (transaction: Transaction) => void;
}

export function TransactionList({ 
  transactions, 
  showGrouped = false,
  filterByType,
  onTransactionClick 
}: TransactionListProps) {
  const filteredTransactions = useMemo(() => {
    if (!filterByType) return transactions;
    return transactions.filter(t => filterByType.includes(t.type));
  }, [transactions, filterByType]);
  
  const groupedTransactions = useMemo(() => {
    if (!showGrouped) return null;
    return groupBy(filteredTransactions, 'transaction_group_id');
  }, [filteredTransactions, showGrouped]);
  
  // Renderizado de transacciones...
}
```

### Fase 6: Testing y Validación

#### 6.1 Tests de Migración

```php
class TransactionMigrationTest extends TestCase
{
    public function test_payment_migration_creates_correct_transactions()
    {
        // Crear pago de prueba
        $payment = Payment::factory()->completed()->create([
            'amount' => 100.00,
            'currency' => 'USD',
        ]);
        
        // Ejecutar migración
        Artisan::call('migrate:payments-to-transactions');
        
        // Verificar transacciones creadas
        $paymentTransaction = Transaction::where('type', Transaction::TYPE_PAYMENT)
            ->where('related_model_id', $payment->agreement_id)
            ->first();
            
        $this->assertNotNull($paymentTransaction);
        $this->assertEquals(-100.00, $paymentTransaction->amount);
        $this->assertEquals($payment->payer_user_id, $paymentTransaction->user_id);
        
        $depositTransaction = Transaction::where('type', Transaction::TYPE_DEPOSIT)
            ->where('parent_transaction_id', $paymentTransaction->id)
            ->first();
            
        $this->assertNotNull($depositTransaction);
        $this->assertEquals(100.00, $depositTransaction->amount);
        $this->assertEquals($payment->payee_user_id, $depositTransaction->user_id);
    }
}
```

## Cronograma de Implementación

### Semana 1-2: Preparación
- [ ] Análisis detallado de datos existentes
- [ ] Diseño de esquema de base de datos
- [ ] Creación de migraciones
- [ ] Setup de entorno de testing

### Semana 3-4: Backend Core
- [ ] Actualización del modelo Transaction
- [ ] Creación de servicios de negocio
- [ ] Implementación de migración de datos
- [ ] Tests unitarios

### Semana 5-6: Refactorización
- [ ] Actualización de modelos existentes
- [ ] Refactorización de controladores
- [ ] Actualización de APIs
- [ ] Tests de integración

### Semana 7-8: Frontend
- [ ] Actualización de tipos TypeScript
- [ ] Refactorización de componentes
- [ ] Actualización de páginas de transacciones
- [ ] Tests E2E

### Semana 9-10: Testing y Deploy
- [ ] Testing completo del sistema
- [ ] Migración de datos en staging
- [ ] Performance testing
- [ ] Deploy a producción

## Beneficios Esperados

### Técnicos
1. **Arquitectura Más Limpia**: Separación clara de responsabilidades
2. **Mejor Auditoría**: Historial completo de todas las operaciones financieras
3. **Escalabilidad**: Fácil agregar nuevos tipos de transacciones
4. **Mantenibilidad**: Código más organizado y testeable
5. **Performance**: Consultas más eficientes con índices optimizados

### De Negocio
1. **Transparencia**: Visibilidad completa de movimientos financieros
2. **Compliance**: Mejor cumplimiento de regulaciones financieras
3. **Reportes**: Capacidades avanzadas de reporting y analytics
4. **Flexibilidad**: Soporte para nuevos modelos de negocio
5. **Confianza**: Mayor confianza de usuarios en el manejo financiero

## Riesgos y Mitigaciones

### Riesgos Identificados
1. **Pérdida de Datos**: Durante la migración
2. **Downtime**: Tiempo de inactividad durante deploy
3. **Inconsistencias**: Entre datos antiguos y nuevos
4. **Performance**: Impacto en rendimiento durante migración

### Mitigaciones
1. **Backups Completos**: Antes de cada paso de migración
2. **Migración Gradual**: Por lotes pequeños con validación
3. **Rollback Plan**: Procedimiento de reversión documentado
4. **Monitoring**: Monitoreo intensivo durante la migración
5. **Testing Exhaustivo**: En ambiente de staging idéntico a producción

## Conclusión

Esta refactorización transformará el sistema de una arquitectura centrada en pagos a una centrada en transacciones, proporcionando mayor flexibilidad, mejor auditoría y preparando el sistema para futuras expansiones del modelo de negocio.

La implementación gradual y el testing exhaustivo asegurarán una transición suave sin impacto en los usuarios finales.