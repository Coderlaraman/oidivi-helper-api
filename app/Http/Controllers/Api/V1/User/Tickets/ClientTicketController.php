<?php

namespace App\Http\Controllers\Api\V1\User\Tickets;

use App\Http\Controllers\Api\V1\Client\Tickets\ClientTicketReplyResource;
use App\Http\Controllers\Api\V1\Client\Tickets\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserTicketResource;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientTicketController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'category' => 'required|in:account,payment,technical,other',
                'message' => 'required|string'
            ]);

            $ticket = Ticket::create([
                'user_id' => auth()->id(),
                ...$validated,
                'status' => 'open'
            ]);

            return $this->successResponse(
                new UserTicketResource($ticket),
                __('messages.tickets.created'),
                201
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                __('messages.validation_error'),
                422,
                $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.tickets.create_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function index(): JsonResponse
    {
        try {
            $tickets = Ticket::where('user_id', auth()->id())
                ->with(['replies', 'user'])
                ->latest()
                ->get();

            return $this->successResponse(
                UserTicketResource::collection($tickets),
                __('messages.tickets.list_success')
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.tickets.list_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function show(Ticket $ticket): JsonResponse
    {
        try {
            if ($ticket->user_id !== auth()->id()) {
                return $this->errorResponse(
                    __('messages.unauthorized'),
                    403
                );
            }

            return $this->successResponse(
                new UserTicketResource($ticket->load(['replies', 'user'])),
                __('messages.tickets.show_success')
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.tickets.show_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function reply(Request $request, Ticket $ticket): JsonResponse
    {
        try {
            if ($ticket->user_id !== auth()->id()) {
                return $this->errorResponse(
                    __('messages.unauthorized'),
                    403
                );
            }

            $validated = $request->validate([
                'message' => 'required|string'
            ]);

            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $validated['message']
            ]);

            return $this->successResponse(
                new ClientTicketReplyResource($reply),
                __('messages.tickets.reply_sent'),
                201
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                __('messages.validation_error'),
                422,
                $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                __('messages.tickets.reply_error'),
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
