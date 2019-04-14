<?php namespace Naraki\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $primaryKey = 'product_id';
    protected $fillable = [
        'product_name',
        'product_identifier',
        'product_packager_id',
        'created_at'
    ];
}
