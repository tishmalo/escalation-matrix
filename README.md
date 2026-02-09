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

4.  **Setup Ticket Authentication (Important!):**
    The `/tickets` interface is protected and requires authentication. Choose one of the methods below:

### Ticket Authentication Setup

#### Option 1: For Apps WITH Laravel Authentication (Recommended)

If your application has user authentication (Breeze, Jetstream, Sanctum, etc.), configure allowed users:

```env
ESCALATION_AUTH_ENABLED=true
ESCALATION_ALLOWED_EMAILS=admin@company.com,dev@company.com,support@company.com
```

Or use roles (works with Spatie Permission, Laravel's built-in roles, etc.):

```env
ESCALATION_AUTH_ENABLED=true
ESCALATION_ALLOWED_ROLES=admin,developer,support
```

**How it works:**
- Users must log in to your application first
- Package checks if the logged-in user's email is in allowed list OR has an allowed role
- No additional login required - uses your app's existing authentication

#### Option 2: For Apps WITHOUT Authentication (Password-Based)

If your application doesn't have authentication, set up a password:

```bash
php artisan escalation:set-password
```

This command will:
1. Prompt you to enter a password (min 8 characters)
2. Generate a bcrypt hash
3. Show you what to add to your `.env` file

Add the generated hash to `.env`:

```env
ESCALATION_AUTH_ENABLED=true
ESCALATION_PASSWORD_HASH="$2y$10$abc...xyz"
```

**How it works:**
- Users visit `/tickets` and see a login form
- They enter the password you set
- Session-based authentication (login once per browser session)
- To change password, run `php artisan escalation:set-password` again

#### Disable Authentication (Not Recommended for Production)

To disable authentication (only for local development):

```env
ESCALATION_AUTH_ENABLED=false
```

**⚠️ Warning:** This exposes sensitive error data (stack traces, user info, IPs) to anyone who can access your application.

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

### ✨ Automatic Exception Capture (Zero Configuration Required)

**The package works automatically!** Once installed and configured, it will capture and process ALL exceptions without any code changes.

The package automatically registers with Laravel's exception handler using the `reportable()` method and processes exceptions based on your `config/escalation.php` settings.

**To disable automatic reporting:**
```env
# In your .env file
ERROR_AUTO_REPORT=false
```

Or in `config/escalation.php`:
```php
'auto_report' => false,
```

### Manual Integration (Optional)

If you prefer manual control or disabled auto-reporting, you can manually trigger the escalation:

**Using Facade:**
```php
use Tishmalo\EscalationMatrix\Facades\Escalation;

try {
    // ... your code
} catch (\Throwable $e) {
    Escalation::handle($e);
    throw $e; // Re-throw if needed
}
```

**Using Service Container:**
```php
app(\Tishmalo\EscalationMatrix\Services\EscalationService::class)->handle($exception);
```

### Viewing and Managing Tickets

Access the ticket management interface at `/tickets` to:
- View all tickets with their priorities and statuses
- Click on individual tickets to see full details
- Change ticket status via dropdown (Open, In Progress, Resolved, Closed)

### Updating the Package

When updating the package, if you have previously published the views, republish them to get the latest updates:

```bash
php artisan vendor:publish --tag=escalation-views --force
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
