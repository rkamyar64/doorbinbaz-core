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
        Schema::create('aria_crons', function (Blueprint $table) {
            $table->id();
            $table->integer('wc_id');
            $table->string('name');
            $table->string('slug');
            $table->string('permalink');
            $table->integer('sku');
            $table->longText('description');
            $table->string('price');
            $table->string('regular_price');
            $table->longText('images');
            $table->string('is_in_stock');
            $table->string('maximum');
            $table->longText('other');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aria_crons');
    }
};
