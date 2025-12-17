<?php

namespace App\Console\Commands;

use App\Services\MqttUserService;
use Illuminate\Console\Command;

class MqttUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:user {action} {username} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage MQTT broker users (add/remove)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $username = $this->argument('username');
        $password = $this->argument('password');

        $mqttService = new MqttUserService();

        switch ($action) {
            case 'add':
                if (!$password) {
                    $password = $this->secret('Enter password for MQTT user:');
                }

                $this->info("Adding user to MQTT broker...");

                if ($mqttService->addMqttUser($username, $password)) {
                    $this->info("✓ Successfully added user: {$username}");
                } else {
                    $this->error("✗ Failed to add user: {$username}");
                    return 1;
                }
                break;

            case 'remove':
                $this->info("Removing user from MQTT broker...");

                if ($mqttService->removeMqttUser($username)) {
                    $this->info("✓ Successfully removed user: {$username}");
                } else {
                    $this->error("✗ Failed to remove user: {$username}");
                    return 1;
                }
                break;

            default:
                $this->error("Invalid action. Use 'add' or 'remove'");
                $this->info("\nUsage:");
                $this->info("  php artisan mqtt:user add username@example.com password123");
                $this->info("  php artisan mqtt:user remove username@example.com");
                return 1;
        }

        return 0;
    }
}
