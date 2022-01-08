<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavouriteBlogs extends Model
{
    use HasFactory;

    protected $table = 'favouriteblog';
    protected $guarded = ['id'];

}
