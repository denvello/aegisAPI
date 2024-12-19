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
        Schema::create('users', function (Blueprint $table) {
            $table->unsignedInteger('id', true); // INT Primary Key, Auto Increment
            $table->string('email', 255)->unique()->notNullable(); // VARCHAR(255), Unique, Not Null
            $table->string('password', 255)->notNullable(); // VARCHAR(255), Not Null
            $table->string('name', 255)->notNullable(); // VARCHAR(255), Not Null
            $table->boolean('active')->default(true); // BOOLEAN, Default: true
            $table->timestamps(); // created_at (Default: Current Timestamp), updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
