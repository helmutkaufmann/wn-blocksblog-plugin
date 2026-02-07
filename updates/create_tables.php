<?php namespace Mercator\BlocksBlog\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateTables extends Migration
{
    public function up()
    {
        Schema::create('mercator_blocksblog_categories', function($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('mercator_blocksblog_posts', function($table) {
            $table->increments('id');
            $table->string('title');
            $table->string('slug')->index();
            $table->integer('author_id')->unsigned()->nullable()->index();
            $table->boolean('is_published')->default(false)->index();
            $table->dateTime('published_at')->nullable()->index();
            $table->mediumText('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();
        });

        Schema::create('mercator_blocksblog_post_categories', function($table) {
            $table->integer('post_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->primary(['post_id', 'category_id']);
            $table->index(['category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mercator_blocksblog_post_categories');
        Schema::dropIfExists('mercator_blocksblog_posts');
        Schema::dropIfExists('mercator_blocksblog_categories');
    }
}
