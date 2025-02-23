<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('namespace');
            $table->string('type');
            $table->string('status');
            $table->integer('replica');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('health_status')->nullable();
            $table->unsignedBigInteger('subserviceID')->nullable();
            $table->string('ready')->nullable();

            // Foreign key constraint
            $table->foreign('subserviceID')
                ->references('id')->on('subservices')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('resources');
    }
};

