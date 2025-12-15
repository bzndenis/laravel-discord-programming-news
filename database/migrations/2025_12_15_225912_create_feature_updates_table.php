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
        Schema::create('feature_updates', function (Blueprint $table) {
            $table->id();
            $table->string('source_name'); // e.g. laravel, nodejs
            $table->string('version')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('hash')->unique(); // Unique identifier (md5 of source+version)
            $table->timestamp('published_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_updates');
    }
};
