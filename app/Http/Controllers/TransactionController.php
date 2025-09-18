<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;
use App\Services\PaymentService;
use App\Http\Resources\User\UserTransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Exception;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private PaymentService $paymentService
    ) {}

    /**
     * Display a listing of the user's transactions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Usar el servicio para obtener transacciones con filtros
        $filters = $request->only([
            'type', 'status', 'role', 'date_from', 'date_to', 
            'amount_min', 'amount_max', 'search', 'transaction_group_id'
        ]);
        
        $transactions = $this->transactionService->getTransactionHistory($user, $filters);
        
        return response()->json([
            'success' => true,
            'data' => UserTransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $this->authorize('view', $transaction);
        
        $transaction->load(['relatedModel', 'parentTransaction', 'childTransactions']);
        
        return response()->json([
            'success' => true,
            'data' => new UserTransactionResource($transaction),
        ]);
    }

    /**
     * Get transaction statistics for the authenticated user.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30_days'); // 7_days, 30_days, 90_days, 1_year
        
        $stats = $this->transactionService->getUserStatistics($user, $period);
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get user balance information.
     */
    public function balance(): JsonResponse
    {
        $user = Auth::user();
        $balance = $this->transactionService->calculateUserBalance($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'available_balance' => $balance['available'],
                'pending_balance' => $balance['pending'],
                'total_earned' => $balance['total_earned'],
                'total_spent' => $balance['total_spent'],
                'total_withdrawn' => $balance['total_withdrawn'],
                'commission_paid' => $balance['commission_paid'],
            ],
        ]);
    }

    /**
     * Process a refund for a payment.
     */
    public function processRefund(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:500',
        ]);
        
        try {
            $payment = \App\Models\Payment::findOrFail($request->payment_id);
            $this->authorize('refund', $payment);
            
            $result = $this->paymentService->processRefund(
                $payment,
                $request->amount,
                $request->reason
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => [
                    'refund_transaction' => new UserTransactionResource($result['refund_transaction']),
                    'transaction_group_id' => $result['transaction_group_id'],
                    'refunded_amount' => $result['refunded_amount'],
                ],
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get transactions grouped by transaction_group_id.
     */
    public function getTransactionGroup(string $groupId): JsonResponse
    {
        $user = Auth::user();
        
        $transactions = Transaction::where('transaction_group_id', $groupId)
            ->where('user_id', $user->id)
            ->with(['relatedModel', 'parentTransaction', 'childTransactions'])
            ->orderBy('created_at')
            ->get();
            
        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction group not found',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'group_id' => $groupId,
                'transactions' => UserTransactionResource::collection($transactions),
                'summary' => [
                    'total_amount' => $transactions->sum('amount'),
                    'transaction_count' => $transactions->count(),
                    'status' => $transactions->first()->status,
                    'created_at' => $transactions->first()->created_at,
                ],
            ],
        ]);
    }

    /**
     * Legacy method - kept for backward compatibility.
     * 
     * @deprecated Use the new methods above
     */
    private function legacyIndex(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = $user->transactions()
                     ->with(['relatedModel'])
                     ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by role (client or helper)
        if ($request->has('role')) {
            if ($request->role === 'client') {
                $query->where('amount', '<', 0); // Outgoing transactions
            } elseif ($request->role === 'helper') {
                $query->where('amount', '>', 0); // Incoming transactions
            }
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by amount range
        if ($request->has('amount_min')) {
            $query->where('amount', '>=', abs($request->amount_min));
        }

        if ($request->has('amount_max')) {
            $query->where('amount', '<=', abs($request->amount_max));
        }

        $transactions = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'summary' => [
                'total_earnings' => $user->total_earnings,
                'total_spent' => $user->total_spent,
                'balance' => $user->transaction_balance,
            ]
        ]);
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $user = Auth::user();

        // Ensure user can only view their own transactions
        if ($transaction->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to transaction'
            ], 403);
        }

        $transaction->load(['relatedModel', 'user']);

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * Get transaction statistics for the authenticated user.
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'total_transactions' => $user->transactions()->count(),
            'completed_transactions' => $user->completedTransactions()->count(),
            'pending_transactions' => $user->pendingTransactions()->count(),
            'total_earnings' => $user->total_earnings,
            'total_spent' => $user->total_spent,
            'balance' => $user->transaction_balance,
            'transactions_by_type' => $user->completedTransactions()
                ->selectRaw('type, COUNT(*) as count, SUM(amount) as total_amount')
                ->groupBy('type')
                ->get(),
            'monthly_summary' => $user->completedTransactions()
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count, SUM(amount) as total_amount')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Export transactions to CSV.
     */
    public function export(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = $user->transactions()
                     ->with(['relatedModel'])
                     ->orderBy('created_at', 'desc');

        // Apply same filters as index method
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('role')) {
            if ($request->role === 'client') {
                $query->where('amount', '<', 0);
            } elseif ($request->role === 'helper') {
                $query->where('amount', '>', 0);
            }
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->get();

        // Format data for CSV export
        $csvData = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'status' => $transaction->status,
                'description' => $transaction->description,
                'processed_at' => $transaction->processed_at?->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $csvData,
            'filename' => 'transactions_' . now()->format('Y-m-d') . '.csv'
        ]);
    }

    /**
     * Get available filter options.
     */
    public function filterOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'types' => [
                    ['value' => 'all', 'label' => 'All Types'],
                    ['value' => Transaction::TYPE_PAYMENT, 'label' => 'Payment'],
                    ['value' => Transaction::TYPE_REFUND, 'label' => 'Refund'],
                    ['value' => Transaction::TYPE_FEE, 'label' => 'Fee'],
                    ['value' => Transaction::TYPE_BONUS, 'label' => 'Bonus'],
                    ['value' => Transaction::TYPE_WITHDRAWAL, 'label' => 'Withdrawal'],
                    ['value' => Transaction::TYPE_DEPOSIT, 'label' => 'Deposit'],
                    ['value' => Transaction::TYPE_COMMISSION, 'label' => 'Commission'],
                    ['value' => Transaction::TYPE_PENALTY, 'label' => 'Penalty'],
                ],
                'statuses' => [
                    ['value' => 'all', 'label' => 'All Statuses'],
                    ['value' => Transaction::STATUS_PENDING, 'label' => 'Pending'],
                    ['value' => Transaction::STATUS_PROCESSING, 'label' => 'Processing'],
                    ['value' => Transaction::STATUS_COMPLETED, 'label' => 'Completed'],
                    ['value' => Transaction::STATUS_FAILED, 'label' => 'Failed'],
                    ['value' => Transaction::STATUS_CANCELLED, 'label' => 'Cancelled'],
                    ['value' => Transaction::STATUS_REFUNDED, 'label' => 'Refunded'],
                ],
                'roles' => [
                    ['value' => 'all', 'label' => 'All Transactions'],
                    ['value' => 'client', 'label' => 'As Client (Outgoing)'],
                    ['value' => 'helper', 'label' => 'As Helper (Incoming)'],
                ]
            ]
        ]);
    }
}