<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('excludes', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->string('name', 100)->nullable();
            $table->string('type', 100);
            $table->timestamps(); // Creates `created_at` and `updated_at`
        });
    }

    public function down() {
        Schema::dropIfExists('excludes');
    }
};
