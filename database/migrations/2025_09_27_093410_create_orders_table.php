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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId("business_id") ->constrained('businesses')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->json("services");
            $table->longText("description")->nullable();
            $table->integer('status')->default(0);
            $table->string('full_price')->default("0")->comment("قیمت کل");
            $table->string('fee_price')->default("0")->comment("اجرت");
            $table->string('profit_price')->default("0")->comment("سود");
            $table->string('discount')->nullable()->comment("تخفیف");
            $table->foreignId('service_user_id')->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('store_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
