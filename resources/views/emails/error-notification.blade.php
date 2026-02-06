<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Notification</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; background-color: #f5f5f5; }
        .container { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { padding: 24px; color: white; }
        .header.critical { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); }
        .header.high { background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); }
        .header.medium { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); }
        .header.low { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 24px; }
        .greeting { font-size: 16px; margin-bottom: 20px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 16px; }
        .badge.critical { background: #fee2e2; color: #991b1b; }
        .badge.high { background: #fed7aa; color: #c2410c; }
        .badge.medium { background: #dbeafe; color: #1e40af; }
        .badge.low { background: #e2e8f0; color: #475569; }
        .section { margin: 24px 0; padding: 16px; background: #f9fafb; border-left: 4px solid #e5e7eb; border-radius: 4px; }
        .section h2 { margin: 0 0 12px; font-size: 16px; font-weight: 600; color: #1f2937; }
        .detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .detail-label { flex: 0 0 180px; font-weight: 600; color: #6b7280; font-size: 14px; }
        .detail-value { flex: 1; color: #1f2937; font-size: 14px; word-break: break-word; }
        .code-block { background: #1f2937; color: #f3f4f6; padding: 16px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 12px; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word; margin: 12px 0; }
        .ticket-box { background: #fef3c7; border: 2px solid #fbbf24; border-radius: 8px; padding: 16px; margin: 20px 0; text-align: center; }
        .ticket-box h3 { margin: 0 0 8px; color: #92400e; font-size: 16px; }
        .ticket-number { font-size: 24px; font-weight: 700; color: #92400e; font-family: monospace; margin: 8px 0; }
        .footer { padding: 20px; background: #f9fafb; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ $priority }}">
            <h1>{{ $errorData['priority_label'] }} Alert</h1>
            <p>{{ config('app.name') }} - Automated Error Monitoring</p>
        </div>

        <div class="content">
            <div class="greeting">Hello <strong>{{ $contact['name'] }}</strong>,</div>
            <p>An error has been detected and requires your attention.</p>
            <span class="badge {{ $priority }}">{{ $errorData['priority_label'] }} Priority</span>

            <div class="section">
                <h2>🔴 Exception Details</h2>
                <div class="detail-row"><div class="detail-label">Type:</div><div class="detail-value"><strong>{{ $errorData['exception']['type_short'] }}</strong></div></div>
                <div class="detail-row"><div class="detail-label">Message:</div><div class="detail-value">{{ $errorData['exception']['message'] }}</div></div>
            </div>

            <div class="section">
                <h2>📍 Location</h2>
                <div class="detail-row"><div class="detail-label">File:</div><div class="detail-value"><code>{{ $errorData['exception']['file'] }}</code></div></div>
                <div class="detail-row"><div class="detail-label">Line:</div><div class="detail-value"><strong>{{ $errorData['exception']['line'] }}</strong></div></div>
            </div>

            @if(!empty($errorData['ticket_number']))
            <div class="ticket-box">
                <h3>✅ Support Ticket Auto-Created</h3>
                <div class="ticket-number">#{{ $errorData['ticket_number'] }}</div>
                <p style="margin: 8px 0; color: #92400e;">A support ticket has been automatically created.</p>
            </div>
            @endif

            <div class="section">
                <h2>🌐 Request Information</h2>
                <div class="detail-row"><div class="detail-label">URL:</div><div class="detail-value"><code>{{ $errorData['request']['method'] }} {{ $errorData['request']['url'] }}</code></div></div>
                <div class="detail-row"><div class="detail-label">IP:</div><div class="detail-value">{{ $errorData['request']['ip'] }}</div></div>
            </div>

            <div class="section">
                <h2>⚙️ Environment</h2>
                <div class="detail-row"><div class="detail-label">Env:</div><div class="detail-value"><strong>{{ strtoupper($errorData['environment']['app_env']) }}</strong></div></div>
                <div class="detail-row"><div class="detail-label">Timestamp:</div><div class="detail-value">{{ $errorData['environment']['timestamp'] }}</div></div>
            </div>

            <div class="section">
                <h2>📋 Stack Trace</h2>
                <div class="code-block">{{ $errorData['exception']['trace'] }}</div>
            </div>
        </div>

        <div class="footer">
            <p>Automated notification from {{ config('app.name') }}</p>
            <p>{{ now()->format('F j, Y \a\t g:i A') }} ({{ config('app.timezone') }})</p>
        </div>
    </div>
</body>
</html>
