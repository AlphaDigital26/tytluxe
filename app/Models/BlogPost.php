<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;
use App\Mail\BlogNewsletterMail;
use App\Models\NewsletterSubscriber;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function booted()
    {
        static::created(function ($blogPost) {
            if ($blogPost->is_active) {
                static::sendNewsletter($blogPost);
            }
        });

        static::updated(function ($blogPost) {
            // Check if is_active was just changed to true
            if ($blogPost->isDirty('is_active') && $blogPost->is_active) {
                static::sendNewsletter($blogPost);
            }
        });
    }

    protected static function sendNewsletter($blogPost)
    {
        $subscribers = NewsletterSubscriber::whereNull('unsubscribed_at')->get();
        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)->send(new BlogNewsletterMail($blogPost));
        }
    }

    protected $casts = [
        'published_at'  => 'datetime',
        'is_trending'   => 'boolean',
        'is_active'     => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true)->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
