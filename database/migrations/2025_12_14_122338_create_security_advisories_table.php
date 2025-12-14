<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security_advisories', function (Blueprint $table) {
            $table->id();
            $table->string('framework_name');
            $table->string('cve_id')->nullable();
            $table->string('severity')->default('HIGH'); // CRITICAL, HIGH
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('reference_url');
            $table->string('hash')->unique()->comment('Unique hash to prevent duplicates');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_advisories');
    }
};
