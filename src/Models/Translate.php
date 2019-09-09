<?php
declare(strict_types = 1);

namespace Krasov\SeoManager\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Translate
 *
 * @package Krasov\SeoManager\Models
 */
class Translate extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'keywords'      => 'array',
        'title_dynamic' => 'array',
        'og_data'       => 'array',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('seo-manager.database.translates_table');

        parent::__construct($attributes);
    }
}
