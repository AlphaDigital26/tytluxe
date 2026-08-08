<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Add all columns that weren't in the original stub migration
            if (!Schema::hasColumn('blog_posts', 'title')) {
                $table->string('title')->after('id');
            }
            if (!Schema::hasColumn('blog_posts', 'slug')) {
                $table->string('slug')->unique()->after('title');
            }
            if (!Schema::hasColumn('blog_posts', 'excerpt')) {
                $table->text('excerpt')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('blog_posts', 'body')) {
                $table->longText('body')->nullable()->after('excerpt');
            }
            if (!Schema::hasColumn('blog_posts', 'cover_image_url')) {
                $table->string('cover_image_url', 1000)->nullable()->after('body');
            }
            if (!Schema::hasColumn('blog_posts', 'blog_category_id')) {
                $table->unsignedBigInteger('blog_category_id')->nullable()->index()->after('cover_image_url');
            }
            if (!Schema::hasColumn('blog_posts', 'read_time_minutes')) {
                $table->unsignedSmallInteger('read_time_minutes')->default(5)->after('blog_category_id');
            }
            if (!Schema::hasColumn('blog_posts', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('read_time_minutes');
            }
            if (!Schema::hasColumn('blog_posts', 'is_trending')) {
                $table->boolean('is_trending')->default(false)->after('published_at');
            }
            if (!Schema::hasColumn('blog_posts', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_trending');
            }
            if (!Schema::hasColumn('blog_posts', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('blog_posts', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'slug', 'excerpt', 'body', 'cover_image_url',
                'blog_category_id', 'read_time_minutes', 'published_at',
                'is_trending', 'is_active', 'sort_order', 'deleted_at',
            ]);
        });
    }
};
