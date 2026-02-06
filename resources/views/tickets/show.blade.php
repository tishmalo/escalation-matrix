<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $ticket->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('escalation.tickets.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">&larr; Back to Tickets</a>
        </div>
        
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">{{ $ticket->subject }}</h1>
                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                    {{ $ticket->priority === 'critical' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst($ticket->priority) }}
                </span>
            </div>
            <div class="p-6">
                <div class="prose max-w-none text-gray-600">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Description</h3>
                    <div class="bg-gray-50 p-4 rounded border border-gray-100 whitespace-pre-wrap font-mono text-sm">
{{ $ticket->description }}
                    </div>
                </div>
                
                @if($ticket->metadata)
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Calculated Metadata</h3>
                    <div class="bg-gray-800 text-green-400 p-4 rounded overflow-auto font-mono text-xs">
{{ json_encode($ticket->metadata, JSON_PRETTY_PRINT) }}
                    </div>
                </div>
                @endif
            </div>
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 text-sm text-gray-500">
                Created {{ $ticket->created_at->format('M d, Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>
