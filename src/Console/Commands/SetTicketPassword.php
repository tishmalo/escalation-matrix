<?php

namespace Tishmalo\EscalationMatrix\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetTicketPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escalation:set-password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set or update the password for accessing escalation tickets';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔐 Escalation Matrix - Set Ticket Access Password');
        $this->newLine();

        $password = $this->secret('Enter new password (min 8 characters)');

        if (empty($password)) {
            $this->error('Password cannot be empty.');
            return 1;
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');
            return 1;
        }

        $confirm = $this->secret('Confirm password');

        if ($password !== $confirm) {
            $this->error('Passwords do not match.');
            return 1;
        }

        $hash = Hash::make($password);

        $this->newLine();
        $this->info('✅ Password hash generated successfully!');
        $this->newLine();
        $this->line('Add this line to your .env file:');
        $this->newLine();
        $this->warn("ESCALATION_PASSWORD_HASH=\"{$hash}\"");
        $this->newLine();
        $this->line('Also ensure authentication is enabled:');
        $this->warn('ESCALATION_AUTH_ENABLED=true');
        $this->newLine();
        $this->info('After updating .env, restart your application to apply changes.');

        return 0;
    }
}
