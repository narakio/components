<?php namespace Naraki\Media\Models;

use Naraki\Core\Support\NestedSet\NodeTrait;
use Naraki\Core\Traits\Enumerable;
use Illuminate\Database\Eloquent\Model;

class MediaGroup extends Model {
    use Enumerable, NodeTrait;

	protected $primaryKey = 'media_group_id';
    protected $fillable = ['media_group_id','media_group_name'];
    protected $guarded = ['parent_id', 'lft', 'rgt'];
    public $timestamps = false;

	const TEXT = 0x001;
	const TEXT_LIBRARY = 0x002;
	const IMAGE = 0x100;
	const IMAGE_GALLERY = 0x102;
	const IMAGE_LIBRARY = 0x103;

}
