<?php

namespace App\Http\Controllers;

use App\Models\Guild;
use App\Models\TicketCategory;
use Illuminate\Http\JsonResponse;

class TicketCategoryController extends Controller
{
    public function show(TicketCategory $ticket_category): JsonResponse
    {
        return response()->json([
            'id' => $ticket_category->id,
            'name' => $ticket_category->name,
            'welcomeMessage' => $ticket_category->initial_message,
            'moderatorRoles' => $ticket_category->moderator_roles,
            'maxTickets' => $ticket_category->max_tickets,
            'discordCategoryId' => $ticket_category->category_id,
        ]);
    }

    public function index(Guild $guild): JsonResponse
    {
        $categories = $guild->ticketCategories()->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'discordCategoryId' => $category->category_id,
                'moderatorRoles' => $category->moderator_roles,
                'welcomeMessage' => $category->initial_message,
                'maxTickets' => $category->max_tickets,
            ];
        });

        return response()->json($categories);
    }
}
