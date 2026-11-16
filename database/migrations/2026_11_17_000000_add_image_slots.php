<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', fn (Blueprint $t) => $t->string('banner')->nullable()->after('image'));
        Schema::table('ads', fn (Blueprint $t) => $t->string('image_mobile')->nullable()->after('image'));
    }

    public function down(): void
    {
        Schema::table('categories', fn (Blueprint $t) => $t->dropColumn('banner'));
        Schema::table('ads', fn (Blueprint $t) => $t->dropColumn('image_mobile'));
    }
};
