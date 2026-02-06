<?php

namespace Tishmalo\EscalationMatrix\Http\Controllers;

use Illuminate\Routing\Controller;
use Tishmalo\EscalationMatrix\Models\PackageTicket;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = PackageTicket::orderBy('created_at', 'desc')->paginate(15);
        return view('escalation-matrix::tickets.index', compact('tickets'));
    }

    public function show($id)
    {
        $ticket = PackageTicket::findOrFail($id);
        return view('escalation-matrix::tickets.show', compact('ticket')); // Assuming we create show.blade.php or just dump for now
    }
}
