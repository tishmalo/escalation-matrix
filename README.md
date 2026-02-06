# Escalation Matrix Package

A robust error handling and escalation system for Laravel applications. This package captures exceptions, prioritizes them, and sends notifications via Email and SMS (optional). It also features a standalone support ticket system via a Driver pattern.

## Features

- 🚨 **Prioritized Error Handling:** Automatically categorizes errors as Critical, High, Medium, or Low.
- 📧 **Email Notifications:** sends detailed error reports to configured contacts.
- 📱 **SMS Alerts:** Optional integration with Africa's Talking for critical mobile alerts.
- 🎫 **Support Ticket Driver:** Plug-and-play interface to connect with any support ticket system.
- 🖥️ **Standalone UI:** Built-in interface at `/tickets` to view and manage internal tickets.
- ⚙️ **Configurable:** Rate limiting, ignored exceptions, and custom escalation matrices.

## Installation

You can install the package via composer:

```bash
composer require tishmalo/escalation-matrix
```

## Setup

1.  **Publish Configuration:**
    ```bash
    php artisan vendor:publish --tag=escalation-config
    ```

2.  **Run Migrations:**
    The package uses a local database ticket system by default. Run migrations to create the required table:
    ```bash
    php artisan migrate
    ```

3.  **Configure Escalation Matrix:**
    Edit `config/escalation.php` to set up your notification contacts, priorities, and channels.

### Custom Support Ticket Driver (Optional)

By default, the package uses `LocalTicketDriver` which stores tickets in your database. To integrate with an external ticketing system:

1.  **Implement the Driver Interface:**
    ```php
    // app/Services/MyTicketDriver.php
    use Tishmalo\EscalationMatrix\Contracts\SupportTicketDriver;

    class MyTicketDriver implements SupportTicketDriver {
        public function createTicket($subject, $description, $priority, $errorData): ?string {
            // Your integration logic (e.g., Zendesk, Jira, etc.)
            return 'TICKET-123';
        }

        public function getSystemUser(): ?object {
             return null;
        }
    }
    ```

2.  **Bind Your Custom Driver:**
    In your `AppServiceProvider`:
    ```php
    $this->app->bind(
        \Tishmalo\EscalationMatrix\Contracts\SupportTicketDriver::class,
        \App\Services\MyTicketDriver::class
    );
    ```

## Usage

The package automatically listens for exceptions if you integrate it into your Exception Handler (`bootstrap/app.php` or `App\Exceptions\Handler.php`).

**Manual Trigger:**
```php
use Tishmalo\EscalationMatrix\Services\EscalationService;

try {
    // ... code
} catch (\Throwable $e) {
    app(EscalationService::class)->handle($e);
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
