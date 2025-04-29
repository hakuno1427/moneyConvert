<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('historical_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_code');
            $table->string('to_code');
            $table->decimal('rate', 18, 8);
            $table->date('date');

            $table->foreign('from_code')->references('code')->on('currencies')->onDelete('cascade');
            $table->foreign('to_code')->references('code')->on('currencies')->onDelete('cascade');

            $table->unique(['from_code', 'to_code', 'date']); // prevent duplicate rates for the same day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_exchange_rates');
    }
};
