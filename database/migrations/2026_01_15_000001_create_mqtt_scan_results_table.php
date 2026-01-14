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
        Schema::create('mqtt_scan_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('scan_history_id')->nullable()->constrained('mqtt_scan_histories')->onDelete('cascade');

            // Target information
            $table->string('ip', 45); // IPv4 or IPv6
            $table->integer('port');

            // Connection status
            $table->string('status', 50); // open, closed, reachable, unreachable, etc.
            $table->text('outcome')->nullable(); // Detailed outcome description

            // Authentication
            $table->string('auth_required', 20)->default('unknown'); // yes, no, unknown
            $table->boolean('anonymous_allowed')->default(false);

            // TLS/Certificate information
            $table->boolean('tls')->default(false);
            $table->json('cert_subject')->nullable();
            $table->json('cert_issuer')->nullable();
            $table->timestamp('cert_not_before')->nullable();
            $table->timestamp('cert_not_after')->nullable();
            $table->text('cert_error')->nullable();

            // Topic information
            $table->integer('sys_topic_count')->default(0);
            $table->integer('regular_topic_count')->default(0);
            $table->integer('retained_count')->default(0);
            $table->json('topics')->nullable();

            // Publisher information (messages captured)
            $table->json('publishers')->nullable(); // Array of {topic, payload, qos, retained}

            // Additional metadata
            $table->text('error')->nullable();
            $table->float('response_time')->nullable(); // seconds

            $table->timestamps();

            // Indexes for faster queries
            $table->index(['user_id', 'created_at']);
            $table->index(['ip', 'port']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mqtt_scan_results');
    }
};
