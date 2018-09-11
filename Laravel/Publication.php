<?php

namespace App;

use App\Contracts\HasComments;
use App\Contracts\HasImage;
use App\Contracts\HasMainContent;
use App\Repositories\ImageRepository;
use App\Scopes\HasContentScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Class Publication
 * @package App
 */
class Publication extends Model implements HasImage, HasComments, HasMainContent
{
    /**
     * @var string
     */
    protected $table = 'publications';

    /**
     * @var array
     */
    protected $hidden = ['main_content'];

    /**
     * @var array
     */
    protected $with = ['main_content'];

    /**
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'keywords',
        'content',
        'image',
        'views',
    ];

    /**
     * @var array
     */
    public $images = [
        'image' => [
            'preview',
            'small'
        ]
    ];


    protected $appends = [
        'url',
        'title',
        'description',
        'content',
        'keywords',
        'preview_image_url',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new HasContentScope());
    }

    /**
     * @return MorphOne
     */
    public function main_content(): MorphOne
    {
        return $this->morphOne(MainContent::class, 'contentable');
    }

    /**
     * @return null|string
     */
    public function getTitleAttribute(): ?string
    {
        if (!$this->main_content) {
            return null;
        }

        return $this->main_content->title;
    }

    /**
     * @param $value
     */
    public function setTitleAttribute($value): void
    {
        $this->main_content->attributes['title'] = $value;
    }

    /**
     * @return null|string
     */
    public function getKeywordsAttribute(): ?string
    {
        if (!$this->main_content) {
            return null;
        }

        return $this->main_content->keywords;
    }

    /**
     * @param $value
     */
    public function setKeywordsAttribute($value): void
    {
        $this->main_content->attributes['keywords'] = $value;
    }

    /**
     * @return null|string
     */
    public function getDescriptionAttribute(): ?string
    {
        if (!$this->main_content) {
            return null;
        }

        return $this->main_content->description;
    }

    /**
     * @param $value
     */
    public function setDescriptionAttribute($value): void
    {
        $this->main_content->attributes['description'] = $value;
    }

    /**
     * @return null|string
     */
    public function getContentAttribute(): ?string
    {
        if (!$this->main_content) {
            return null;
        }

        return $this->main_content->content;
    }

    /**
     * @param $value
     */
    public function setContentAttribute($value): void
    {
        $this->main_content->attributes['content'] = $value;
    }

    /**
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * @return string
     */
    public function getUrlAttribute()
    {
        return route('front::publications::show', $this->id);
    }

    /**
     * @return string
     */
    public function getEditAttribute()
    {
        return route('front::publications::edit', $this->id);
    }

    /**
     * @return string
     */
    public function getSmallImageUrlAttribute()
    {
        return ImageRepository::getImageOrHolder($this, 'image', 'small', 'rectangular_picture_holder');
    }

    /**
     * @return string
     */
    public function getPreviewImageUrlAttribute()
    {
        return ImageRepository::getImageOrHolder($this, 'image', 'preview', 'rectangular_picture_holder');
    }

    /**
     * @return string
     */
    public function getOriginalImageUrlAttribute() :string
    {
        return ImageRepository::getImageOrHolder($this, 'image', 'original', 'rectangular_picture_holder');
    }
}
