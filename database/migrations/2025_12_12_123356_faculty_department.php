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
        Schema::create('faculties', function (Blueprint $table){
            $table->id('id_faculty');
            $table->string('name_faculty')->unique();
            $table->timestamps();
        });
        
        Schema::create('departments', function (Blueprint $table){
            $table->id('id_department');
            $table->string('name_department')->unique();
            $table->string('degree_level');
            $table->unsignedBigInteger('id_faculty');
            $table->timestamps();
            
            $table->foreign('id_faculty')->references('id_faculty')->on('faculties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
        Schema::dropIfExists('faculties');
    }
};
