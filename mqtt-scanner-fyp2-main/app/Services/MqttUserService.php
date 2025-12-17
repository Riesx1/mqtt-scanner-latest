<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class MqttUserService
{
    protected $passwordFilePath;
    protected $dockerComposePath;
    protected $dockerPath;

    public function __construct()
    {
        $this->passwordFilePath = base_path('mqtt-brokers/secure/config/passwordfile');
        $this->dockerComposePath = base_path('mqtt-brokers');

        // Set Docker executable path for Windows
        $this->dockerPath = $this->findDockerPath();
    }

    /**
     * Find Docker executable path
     *
     * @return string
     */
    protected function findDockerPath(): string
    {
        // Try common Docker paths on Windows
        $paths = [
            'C:\\Program Files\\Docker\\Docker\\resources\\bin\\docker.exe',
            'C:\\Program Files\\Docker\\Docker\\resources\\bin\\docker',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return "\"{$path}\"";
            }
        }

        // Fallback to just 'docker' command
        return 'docker';
    }

    /**
     * Add a new user to the MQTT broker password file
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function addMqttUser(string $username, string $password): bool
    {
        try {
            // Generate hashed password using mosquitto_passwd
            $hashedPassword = $this->generateMosquittoPassword($username, $password);

            if (!$hashedPassword) {
                Log::error("Failed to generate Mosquitto password for user: {$username}");
                return false;
            }

            // Add user to password file
            $this->addToPasswordFile($username, $hashedPassword);

            // Reload Mosquitto container to apply changes
            $this->reloadMosquittoContainer();

            Log::info("Successfully added MQTT user: {$username}");
            return true;

        } catch (\Exception $e) {
            Log::error("Error adding MQTT user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate Mosquitto password hash
     *
     * @param string $username
     * @param string $password
     * @return string|null
     */
    protected function generateMosquittoPassword(string $username, string $password): ?string
    {
        // Try using mosquitto_passwd command (if available)
        if ($this->commandExists('mosquitto_passwd')) {
            $tempFile = tempnam(sys_get_temp_dir(), 'mqtt_');

            $result = Process::run("mosquitto_passwd -b {$tempFile} {$username} {$password}");

            if ($result->successful()) {
                $content = file_get_contents($tempFile);
                unlink($tempFile);
                return trim(explode(':', $content)[1] ?? '');
            }
        }

        // Fallback: Use Docker to generate password
        return $this->generatePasswordViaDocker($username, $password);
    }

    /**
     * Generate password using Docker mosquitto_passwd
     *
     * @param string $username
     * @param string $password
     * @return string|null
     */
    protected function generatePasswordViaDocker(string $username, string $password): ?string
    {
        try {
            // Escape for Docker command (simple quote escaping for sh -c)
            $username = str_replace("'", "'\\''", $username);
            $password = str_replace("'", "'\\''", $password);

            // Use Docker to run mosquitto_passwd and capture output
            $command = "{$this->dockerPath} exec mosq_secure sh -c \"mosquitto_passwd -b -c /tmp/temp_pass '{$username}' '{$password}' && cat /tmp/temp_pass && rm /tmp/temp_pass\"";

            $result = Process::run($command);

            if ($result->successful()) {
                $output = trim($result->output());

                // Extract the hashed password (everything after the colon)
                // Handle multiline output by taking the last line
                $lines = explode("\n", $output);
                $lastLine = trim(end($lines));

                if (preg_match('/^[^:]+:(.+)$/', $lastLine, $matches)) {
                    return trim($matches[1]);
                }

                Log::error("Could not parse mosquitto_passwd output: " . $output);
            } else {
                Log::error("Docker command failed: " . $result->errorOutput());
            }

            return null;

        } catch (\Exception $e) {
            Log::error("Error generating password via Docker: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add user to password file
     *
     * @param string $username
     * @param string $hashedPassword
     * @return void
     */
    protected function addToPasswordFile(string $username, string $hashedPassword): void
    {
        $entry = "{$username}:{$hashedPassword}\n";

        // Check if user already exists
        if (file_exists($this->passwordFilePath)) {
            $content = file_get_contents($this->passwordFilePath);

            // Remove existing entry for this user
            $lines = explode("\n", $content);
            $filteredLines = array_filter($lines, function ($line) use ($username) {
                return !str_starts_with($line, $username . ':');
            });

            $content = implode("\n", $filteredLines);
            if (!empty($content) && !str_ends_with($content, "\n")) {
                $content .= "\n";
            }

            file_put_contents($this->passwordFilePath, $content . $entry);
        } else {
            // Create new password file
            file_put_contents($this->passwordFilePath, $entry);
        }
    }

    /**
     * Reload Mosquitto container to apply password changes
     *
     * @return void
     */
    protected function reloadMosquittoContainer(): void
    {
        try {
            // Send SIGHUP to Mosquitto to reload config
            $result = Process::run("{$this->dockerPath} exec mosq_secure pkill -HUP mosquitto");

            if (!$result->successful()) {
                // If SIGHUP fails, try restarting the container
                Process::run("{$this->dockerPath} restart mosq_secure");
            }

        } catch (\Exception $e) {
            Log::warning("Could not reload Mosquitto container: " . $e->getMessage());
        }
    }

    /**
     * Check if a command exists
     *
     * @param string $command
     * @return bool
     */
    protected function commandExists(string $command): bool
    {
        $result = Process::run("where {$command}"); // Windows
        if ($result->successful()) {
            return true;
        }

        $result = Process::run("which {$command}"); // Unix/Linux
        return $result->successful();
    }

    /**
     * Remove a user from MQTT broker
     *
     * @param string $username
     * @return bool
     */
    public function removeMqttUser(string $username): bool
    {
        try {
            if (!file_exists($this->passwordFilePath)) {
                return true;
            }

            $content = file_get_contents($this->passwordFilePath);
            $lines = explode("\n", $content);

            $filteredLines = array_filter($lines, function ($line) use ($username) {
                return !str_starts_with($line, $username . ':');
            });

            file_put_contents($this->passwordFilePath, implode("\n", $filteredLines));

            $this->reloadMosquittoContainer();

            Log::info("Successfully removed MQTT user: {$username}");
            return true;

        } catch (\Exception $e) {
            Log::error("Error removing MQTT user: " . $e->getMessage());
            return false;
        }
    }
}
