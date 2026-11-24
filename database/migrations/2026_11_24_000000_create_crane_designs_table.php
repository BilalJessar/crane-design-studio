<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crane_designs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('crane_type', ['single_girder'])->default('single_girder');
            // Capacity is in metric tonnes. All lengths are in millimeters.
            $table->decimal('capacity', 8, 2);
            $table->unsignedInteger('span');
            $table->unsignedInteger('lifting_height');
            $table->unsignedInteger('girder_depth');
            $table->unsignedInteger('girder_width');
            $table->unsignedInteger('end_carriage_length');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crane_designs');
    }
};
