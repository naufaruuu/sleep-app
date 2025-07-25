<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); // AUTO_INCREMENT UNSIGNED BIGINT PRIMARY KEY
            $table->string('name');
            $table->string('description')->nullable();
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
            $table->boolean('isDuration')->default(false);
            $table->boolean('isFixed')->default(false);
        });
    }

    public function down() {
        Schema::dropIfExists('projects');
    }
};
