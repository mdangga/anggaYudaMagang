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
        Schema::create('category', function (Blueprint $table){
            $table->id('id_category');
            $table->string('name_category');
            $table->timestamps();
        });

        Schema::create('location_request', function (Blueprint $table){
            $table->id('id_request');
            $table->string('student_name');
            $table->string('nim', 10);
            $table->string('name_location');
            $table->text('description');
            $table->decimal('longitude', 11, 8);
            $table->decimal('latitude', 10, 8);
            $table->text('image_path');
            $table->unsignedBigInteger('id_category');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('id_category')->references('id_category')->on('category')->onDelete('cascade');
        });

        Schema::create('location', function (Blueprint $table){
            $table->id('id_location');
            $table->string('name_location');
            $table->text('description');
            $table->decimal('longitude', 11, 8);
            $table->decimal('latitude', 10, 8);
            $table->text('image_path');
            $table->unsignedBigInteger('id_category');
            $table->timestamps();

            $table->foreign('id_category')->references('id_category')->on('category')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location');
        Schema::dropIfExists('location_request');
        Schema::dropIfExists('category');
    }
};
