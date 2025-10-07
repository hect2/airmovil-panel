<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryCar extends Model
{
    use HasFactory;

    protected $table = "category_cars";
    protected $fillable = ['name', 'category', 'description', 'status', 'image'];
}
