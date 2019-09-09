<?php
declare(strict_types = 1);

namespace Krasov\SeoManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class SeoManager
 *
 * @package Krasov\SeoManager\Models
 */
class SeoManager extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    protected $locale;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uri',
        'params',
        'mapping',
        'keywords',
        'description',
        'title',
        'author',
        'url',
        'title_dynamic',
        'og_data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'params'        => 'array',
        'mapping'       => 'array',
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
        $this->table = config('seo-manager.database.table');
        $this->locale = app()->getLocale();

        parent::__construct($attributes);
    }

    /**
     * @return HasOne
     */
    public function translation(): HasOne
    {
        return $this->hasOne(Translate::class, 'route_id', 'id')->where('locale', app()->getLocale());
    }

    /**
     * @return bool
     */
    private function isNotDefaultLocale(): bool
    {
        return $this->locale !== config('seo-manager.locale') && $this->has('translation');
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function getKeywordsAttribute($value)
    {
        if ($this->isNotDefaultLocale() && !empty(optional($this->translation)->keywords)) {
            return $this->translation->keywords;
        }

        return json_decode($value ?? '');
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function getDescriptionAttribute($value)
    {
        if ($this->isNotDefaultLocale() && optional($this->translation)->description !== null) {
            return $this->translation->description;
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function getTitleAttribute($value)
    {
        if ($this->isNotDefaultLocale() && optional($this->translation)->title !== null) {
            return $this->translation->title;
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function getAuthorAttribute($value)
    {
        if ($this->isNotDefaultLocale() && optional($this->translation)->author !== null) {
            return $this->translation->author;
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function getUrlAttribute($value)
    {
        if ($this->isNotDefaultLocale() && optional($this->translation)->url !== null) {
            return $this->translation->url;
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function getTitleDynamicAttribute($value)
    {
        if ($this->isNotDefaultLocale() && !empty(optional($this->translation)->title_dynamic)) {
            return $this->translation->title_dynamic;
        }

        return json_decode($value ?? '');
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function getOgDataAttribute($value)
    {
        if ($this->isNotDefaultLocale() && optional($this->translation)->og_data !== null) {
            return $this->translation->og_data;
        }

        return json_decode($value ?? '', true);
    }
}
