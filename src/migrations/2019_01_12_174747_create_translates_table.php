<?php
declare(strict_types = 1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateTranslatesTable
 */
class CreateTranslatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('seo-manager.database.translates_table'), static function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('route_id');
            $table->string('locale');
            $table->string('url')->nullable();
            $table->jsonb('keywords')->nullable();
            $table->string('description')->nullable();
            $table->string('title')->nullable();
            $table->string('author')->nullable();
            $table->jsonb('title_dynamic')->nullable();
            $table->jsonb('og_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('seo-manager.database.translates_table'));
    }
}
