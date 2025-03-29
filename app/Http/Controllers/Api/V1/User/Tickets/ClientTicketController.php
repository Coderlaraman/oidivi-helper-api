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
                'Ticket creado exitosamente',
                201
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Error de validación',
                422,
                $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error al crear ticket',
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
                'Tickets recuperados exitosamente'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error al obtener tickets',
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
                    'No autorizado',
                    403
                );
            }

            return $this->successResponse(
                new UserTicketResource($ticket->load(['replies', 'user'])),
                'Ticket recuperado exitosamente'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error al obtener ticket',
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
                    'No autorizado',
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
                'Respuesta enviada exitosamente',
                201
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                'Error de validación',
                422,
                $e->errors()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                'Error al enviar respuesta',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
