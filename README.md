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

2.  **Run Migrations (Optional):**
    If you plan to use the internal ticket system:
    ```bash
    php artisan migrate
    ```

3.  **Implement Support Driver:**
    Create a class that implements `Tishmalo\EscalationMatrix\Contracts\SupportTicketDriver`.

    ```php
    // app/Services/MyTicketDriver.php
    use Tishmalo\EscalationMatrix\Contracts\SupportTicketDriver;

    class MyTicketDriver implements SupportTicketDriver {
        public function createTicket($subject, $description, $priority, $errorData): ?string {
            // Your logic here
            return 'TICKET-123';
        }
        
        public function getSystemUser(): ?object {
             return null;
        }
    }
    ```

4.  **Bind the Driver:**
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
