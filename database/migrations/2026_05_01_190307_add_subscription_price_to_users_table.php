<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('subscription_price')->default(0)->after('is_vip');
            $table->string('cover_image')->nullable()->after('avatar');
            $table->boolean('is_creator')->default(false)->after('is_vip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['subscription_price', 'cover_image', 'is_creator']);
        });
    }
};
