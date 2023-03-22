<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionMessage extends Model
{
    use HasFactory;

    public $table = 'discussion_messages';

    protected $fillable = [
        'discussion_id',
        'user_id',
        'message',
    ];
}
