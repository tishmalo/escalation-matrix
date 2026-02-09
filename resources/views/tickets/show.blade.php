<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ticket #{{ $ticket->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-4 flex justify-between items-center">
            <a href="{{ route('escalation.tickets.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">&larr; Back to Tickets</a>
            <form method="POST" action="{{ route('escalation.logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-600 hover:text-red-600 font-medium">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout
                </button>
            </form>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">{{ $ticket->subject }}</h1>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                        {{ $ticket->priority === 'critical' ? 'bg-red-100 text-red-800' :
                           ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-800' :
                           ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                    <div class="relative">
                        <select id="statusSelect" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500
                            {{ $ticket->status === 'open' ? 'bg-blue-50 text-blue-700 border-blue-300' :
                               ($ticket->status === 'in_progress' ? 'bg-yellow-50 text-yellow-700 border-yellow-300' :
                               ($ticket->status === 'resolved' ? 'bg-green-50 text-green-700 border-green-300' : 'bg-gray-50 text-gray-700 border-gray-300')) }}">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </div>
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

    <script>
        document.getElementById('statusSelect').addEventListener('change', function() {
            const newStatus = this.value;
            const ticketId = {{ $ticket->id }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            fetch(`/tickets/${ticketId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update select styling based on new status
                    const select = document.getElementById('statusSelect');
                    select.className = select.className.replace(/bg-\w+-50 text-\w+-700 border-\w+-300/g, '');

                    if (newStatus === 'open') {
                        select.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-300');
                    } else if (newStatus === 'in_progress') {
                        select.classList.add('bg-yellow-50', 'text-yellow-700', 'border-yellow-300');
                    } else if (newStatus === 'resolved') {
                        select.classList.add('bg-green-50', 'text-green-700', 'border-green-300');
                    } else {
                        select.classList.add('bg-gray-50', 'text-gray-700', 'border-gray-300');
                    }

                    // Show success message
                    showNotification('Status updated successfully', 'success');
                } else {
                    showNotification('Failed to update status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            });
        });

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} z-50`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>
