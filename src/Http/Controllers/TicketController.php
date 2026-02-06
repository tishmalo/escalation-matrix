<?php

namespace Tishmalo\EscalationMatrix\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Tishmalo\EscalationMatrix\Contracts\SupportTicketDriver;
use Tishmalo\EscalationMatrix\Models\PackageTicket;

class TicketController extends Controller
{
    protected $ticketDriver;

    public function __construct(SupportTicketDriver $ticketDriver)
    {
        $this->ticketDriver = $ticketDriver;
    }

    public function index()
    {
        $tickets = PackageTicket::orderBy('created_at', 'desc')->paginate(15);
        return view('escalation-matrix::tickets.index', compact('tickets'));
    }

    public function show($id)
    {
        $ticket = PackageTicket::findOrFail($id);
        return view('escalation-matrix::tickets.show', compact('ticket'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $success = $this->ticketDriver->updateTicketStatus($id, $request->status);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket status updated successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to update ticket status',
        ], 500);
    }
}
