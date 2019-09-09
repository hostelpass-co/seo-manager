<?php
declare(strict_types = 1);

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateLocalesTable
 */
class CreateLocalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('seo-manager.database.locales_table'), static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        DB::table(config('seo-manager.database.locales_table'))->insert([
            'name'       => config('seo-manager.locale'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('seo-manager.database.locales_table'));
    }
}
