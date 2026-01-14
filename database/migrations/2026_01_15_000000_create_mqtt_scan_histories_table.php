<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mqtt_scan_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Scan parameters
            $table->string('target', 100); // IP or CIDR range scanned
            $table->json('credentials')->nullable(); // Encrypted credentials used (if any)

            // Scan metadata
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->float('duration')->nullable(); // seconds
            $table->string('status', 20)->default('running'); // running, completed, failed

            // Results summary
            $table->integer('total_targets')->default(0);
            $table->integer('reachable_count')->default(0);
            $table->integer('unreachable_count')->default(0);
            $table->integer('vulnerable_count')->default(0); // Anonymous access allowed

            // Request metadata
            $table->string('ip_address', 45)->nullable(); // User's IP
            $table->text('user_agent')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mqtt_scan_histories');
    }
};
