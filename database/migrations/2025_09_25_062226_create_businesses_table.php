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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("family");
            $table->string("business_name");
            $table->longText("address");
            $table->string("mobile")->unique();
            $table->string("tell")->nullable();
            $table->string("zipcode")->nullable();
            $table->string("national_id")->nullable();
            $table->foreignId('store_user_id')
                ->constrained('users')
                ->onDelete('cascade')  // or 'set null', 'restrict'
                ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
