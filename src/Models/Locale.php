<?php
declare(strict_types = 1);

namespace Krasov\SeoManager\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Locale
 *
 * @package Krasov\SeoManager\Models
 */
class Locale extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('seo-manager.database.locales_table');

        parent::__construct($attributes);
    }
}
