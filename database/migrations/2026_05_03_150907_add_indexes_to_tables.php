<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index('post_id');
        });

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('post_id');
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->index('follower_id');
            $table->index('following_id');
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['post_id']);
        });

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['post_id']);
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->dropIndex(['follower_id']);
            $table->dropIndex(['following_id']);
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex(['post_id']);
        });
    }
};
