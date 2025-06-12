<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $table = 'blw_tag_relations';

    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();
            // 防止重复关联
            $table->unique(['subscriber_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
};
