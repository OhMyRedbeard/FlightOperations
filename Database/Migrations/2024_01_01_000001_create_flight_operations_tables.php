<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlightOperationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This module primarily uses existing PHPVMS tables
        // We may add custom tables for enhanced functionality in the future
        
        if (!Schema::hasTable('flight_operations_log')) {
            Schema::create('flight_operations_log', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('flight_id');
                $table->string('action'); // 'created', 'bid_added', 'simbrief_generated', etc.
                $table->json('data')->nullable(); // Additional data for the action
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('flight_id')->references('id')->on('flights')->onDelete('cascade');
                
                $table->index(['user_id', 'created_at']);
                $table->index(['flight_id', 'action']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flight_operations_log');
    }
}
