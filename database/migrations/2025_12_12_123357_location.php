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
        Schema::create('categories', function (Blueprint $table){
            $table->id('id_category');
            $table->string('name_category')->unique();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table){
            $table->id('id_location');
            $table->string('student_name');
            $table->string('nim', 10)->unique();
            $table->string('name_location')->unique();
            $table->text('description');
            $table->string('contact', 15);
            $table->decimal('longitude', 11, 8);
            $table->decimal('latitude', 10, 8);
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('id_category');
            $table->unsignedBigInteger('id_department');
            $table->timestamps();

            $table->unique(['name_location', 'nim']);
            $table->foreign('id_department')->references('id_department')->on('departments')->onDelete('cascade');
            $table->foreign('id_category')->references('id_category')->on('categories')->onDelete('cascade');
        });

        Schema::create('images', function (Blueprint $table){
            $table->id('id_image');
            $table->text('image_path');
            $table->text('alt_text')->nullable();
            $table->unsignedBigInteger('id_location');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_location')->references('id_location')->on('locations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
        Schema::dropIfExists('location');
        Schema::dropIfExists('category');
    }
};
