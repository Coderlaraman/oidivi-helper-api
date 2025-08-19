<?php

namespace App\Http\Controllers\Api\V1\User\Tickets;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Tickets\CreateTicketRequest;
use App\Http\Requests\User\Tickets\UpdateTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserTicketController extends Controller
{
    /**
     * Display a listing of the user's tickets.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tickets = $request->user()
                ->tickets()
                ->with(['replies'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $tickets,
                'message' => 'Tickets retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(CreateTicketRequest $request): JsonResponse
    {
        try {
            $ticket = Ticket::create([
                'user_id' => $request->user()->id,
                'category' => $request->validated('category'),
                'message' => $request->validated('message'),
                'status' => 'open'
            ]);

            $ticket->load(['user', 'replies']);

            return response()->json([
                'success' => true,
                'data' => $ticket,
                'message' => 'Ticket created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified ticket.
     */
    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        try {
            // Ensure the ticket belongs to the authenticated user
            if ($ticket->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this ticket'
                ], 403);
            }

            $ticket->load(['user', 'replies']);

            return response()->json([
                'success' => true,
                'data' => $ticket,
                'message' => 'Ticket retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        try {
            // Ensure the ticket belongs to the authenticated user
            if ($ticket->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this ticket'
                ], 403);
            }

            $ticket->update($request->validated());
            $ticket->load(['user', 'replies']);

            return response()->json([
                'success' => true,
                'data' => $ticket,
                'message' => 'Ticket updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified ticket from storage.
     */
    public function destroy(Request $request, Ticket $ticket): JsonResponse
    {
        try {
            // Ensure the ticket belongs to the authenticated user
            if ($ticket->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this ticket'
                ], 403);
            }

            $ticket->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ticket deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reply to a ticket.
     */
    public function reply(Request $request, Ticket $ticket): JsonResponse
    {
        try {
            // Ensure the ticket belongs to the authenticated user
            if ($ticket->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this ticket'
                ], 403);
            }

            $request->validate([
                'message' => 'required|string|max:2000'
            ]);

            $reply = $ticket->replies()->create([
                'user_id' => $request->user()->id,
                'message' => $request->input('message')
            ]);

            $reply->load('user');

            return response()->json([
                'success' => true,
                'data' => $reply,
                'message' => 'Reply added successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding reply: ' . $e->getMessage()
            ], 500);
        }
    }
}