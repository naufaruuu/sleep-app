<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('subservices', function (Blueprint $table) {
            $table->id();
            $table->timestamps(); // Laravel automatically creates `created_at` & `updated_at`
            $table->string('name');
            $table->unsignedBigInteger('projectID')->nullable();
            $table->integer('active_left')->default(2);
            $table->string('PIC', 100)->nullable();

            // Foreign key constraint
            $table->foreign('projectID')
                ->references('id')->on('projects')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('subservices');
    }
};
