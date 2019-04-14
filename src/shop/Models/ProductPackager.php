<?php namespace Naraki\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPackager extends Model
{
    public $primaryKey = 'product_packager_id';
    public $timestamps = false;
    protected $fillable = [
        'product_packager_id',
        'product_packager_name',
        'product_packager_code',
        'product_packager_emb',
        'product_packager_siret',
        'product_packager_address',
        'product_packager_postcode',
        'product_packager_town',
        'product_packager_category',
        'product_packager_activity',
        'product_packager_species',
    ];

}
