<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_group_id' => $this->transaction_group_id,
            'parent_transaction_id' => $this->parent_transaction_id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'status' => $this->status,
            'amount' => $this->amount,
            'fee_amount' => $this->fee_amount,
            'net_amount' => $this->net_amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'description' => $this->description,
            'reference' => $this->reference,
            'external_id' => $this->external_id,
            'settlement_date' => $this->settlement_date,
            'risk_score' => $this->risk_score,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Computed fields
            'formatted_amount' => $this->getFormattedAmount(),
            'formatted_fee_amount' => $this->getFormattedFeeAmount(),
            'formatted_net_amount' => $this->getFormattedNetAmount(),
            'is_positive' => $this->amount > 0,
            'is_refund' => $this->isRefund(),
            'is_payment' => $this->isPayment(),
            'is_withdrawal' => $this->isWithdrawal(),
            'is_commission' => $this->isCommission(),
            'is_completed' => $this->isCompleted(),
            'is_pending' => $this->isPending(),
            'is_failed' => $this->isFailed(),
            
            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            'related_model' => $this->whenLoaded('relatedModel', function () {
                if ($this->relatedModel) {
                    return [
                        'type' => class_basename($this->relatedModel),
                        'id' => $this->relatedModel->id,
                        'data' => $this->formatRelatedModel($this->relatedModel),
                    ];
                }
                return null;
            }),
            
            'parent_transaction' => $this->whenLoaded('parentTransaction', function () {
                return $this->parentTransaction ? new self($this->parentTransaction) : null;
            }),
            
            'child_transactions' => $this->whenLoaded('childTransactions', function () {
                return self::collection($this->childTransactions);
            }),
            
            // Additional context for UI
            'display_info' => [
                'title' => $this->getDisplayTitle(),
                'subtitle' => $this->getDisplaySubtitle(),
                'icon' => $this->getDisplayIcon(),
                'color' => $this->getDisplayColor(),
            ],
        ];
    }
    
    /**
     * Format related model data based on its type.
     */
    private function formatRelatedModel($model): array
    {
        $baseData = [
            'id' => $model->id,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
        
        switch (class_basename($model)) {
            case 'Payment':
                return array_merge($baseData, [
                    'amount' => $model->amount,
                    'status' => $model->status,
                    'stripe_payment_intent_id' => $model->stripe_payment_intent_id,
                    'agreement_id' => $model->agreement_id,
                ]);
                
            case 'Agreement':
                return array_merge($baseData, [
                    'title' => $model->title,
                    'amount' => $model->amount,
                    'status' => $model->status,
                    'client_id' => $model->client_id,
                    'helper_id' => $model->helper_id,
                ]);
                
            case 'Withdrawal':
                return array_merge($baseData, [
                    'amount' => $model->amount,
                    'status' => $model->status,
                    'bank_account' => $model->bank_account ?? null,
                ]);
                
            default:
                return $baseData;
        }
    }
    
    /**
     * Get display title for the transaction.
     */
    private function getDisplayTitle(): string
    {
        switch ($this->type) {
            case 'payment':
                return $this->amount > 0 ? 'Payment Received' : 'Payment Made';
            case 'refund':
                return $this->amount > 0 ? 'Refund Received' : 'Refund Issued';
            case 'withdrawal':
                return 'Withdrawal';
            case 'commission':
                return 'Platform Commission';
            case 'fee':
                return 'Transaction Fee';
            case 'adjustment':
                return 'Balance Adjustment';
            default:
                return ucfirst($this->type);
        }
    }
    
    /**
     * Get display subtitle for the transaction.
     */
    private function getDisplaySubtitle(): string
    {
        if ($this->relatedModel) {
            switch (class_basename($this->relatedModel)) {
                case 'Agreement':
                    return "Agreement: {$this->relatedModel->title}";
                case 'Payment':
                    return "Payment #{$this->relatedModel->id}";
                default:
                    return $this->description ?? '';
            }
        }
        
        return $this->description ?? $this->reference ?? '';
    }
    
    /**
     * Get display icon for the transaction.
     */
    private function getDisplayIcon(): string
    {
        switch ($this->type) {
            case 'payment':
                return $this->amount > 0 ? 'arrow-down-circle' : 'arrow-up-circle';
            case 'refund':
                return 'arrow-path';
            case 'withdrawal':
                return 'banknotes';
            case 'commission':
                return 'building-office';
            case 'fee':
                return 'receipt-percent';
            default:
                return 'currency-dollar';
        }
    }
    
    /**
     * Get display color for the transaction.
     */
    private function getDisplayColor(): string
    {
        if ($this->isFailed()) {
            return 'red';
        }
        
        if ($this->isPending()) {
            return 'yellow';
        }
        
        switch ($this->type) {
            case 'payment':
                return $this->amount > 0 ? 'green' : 'blue';
            case 'refund':
                return 'orange';
            case 'withdrawal':
                return 'purple';
            case 'commission':
            case 'fee':
                return 'gray';
            default:
                return 'blue';
        }
    }
}