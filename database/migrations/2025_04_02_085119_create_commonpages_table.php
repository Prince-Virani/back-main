<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('commonpages', function (Blueprint $table) {
            $table->id();
            $table->string('page_name');
            $table->text('content');
            $table->tinyInteger('website_id');
            $table->tinyInteger('status_flag')->default(0); // 0 = Inactive, 1 = Active
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commonpages');
    }
};
