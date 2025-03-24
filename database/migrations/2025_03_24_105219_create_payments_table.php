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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('merchantId');
            $table->string('merchantTransactionId');
            $table->string('merchantUserId');
            $table->string('amount');
            $table->string('redirectUrl');
            $table->string('redirectMode');
            $table->string('callbackUrl');
            $table->json('paymentInstrument');
            $table->string('order_id');
            $table->string('status')->default('pending');
            $table->string('payment_id')->nullable();
            $table->string('payment_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
