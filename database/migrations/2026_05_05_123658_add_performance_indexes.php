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
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['sender_id', 'receiver_id', 'created_at'], 'messages_conv_idx');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'posts_user_created_idx');
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->unique(['user_id', 'post_id'], 'likes_user_post_unique');
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->unique(['follower_id', 'following_id'], 'follows_pair_unique');
        });

        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->index(['subscriber_id', 'creator_id', 'expires_at'], 'subscriptions_lookup_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_conv_idx');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_user_created_idx');
        });
        Schema::table('likes', function (Blueprint $table) {
            $table->dropUnique('likes_user_post_unique');
        });
        Schema::table('follows', function (Blueprint $table) {
            $table->dropUnique('follows_pair_unique');
        });
        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropIndex('subscriptions_lookup_idx');
            });
        }
    }
};
