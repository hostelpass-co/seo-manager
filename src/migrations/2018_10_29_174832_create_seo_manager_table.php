<?php
declare(strict_types = 1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateSeoManagerTable
 */
class CreateSeoManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('seo-manager.database.table'), static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('uri')->nullable();
            $table->jsonb('params')->nullable();
            $table->jsonb('mapping')->nullable();
            $table->jsonb('keywords');
            $table->string('description')->nullable();
            $table->string('title')->nullable();
            $table->string('author')->nullable();
            $table->string('url')->nullable();
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
        Schema::dropIfExists(config('seo-manager.database.table'));
    }
}
