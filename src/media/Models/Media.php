<?php namespace Naraki\Media\Models;

use Naraki\Core\Support\NestedSet\NodeTrait;
use Naraki\Core\Traits\Enumerable;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use Enumerable, NodeTrait;

    protected $primaryKey = 'media_id';
    protected $fillable = ['media_id', 'media_name'];
    protected $guarded = ['parent_id', 'lft', 'rgt'];
    public $timestamps = false;

    const DIGITAL = 0x001;
    const VIDEO = 0x010;
    const AUDIO = 0x020;
    const TEXT = 0x030;
    const IMAGE = 0x040;
    const IMAGE_AVATAR = 0x041;
}
