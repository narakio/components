<?php namespace Naraki\Mail\Providers;

use Naraki\Core\EloquentProvider;
use Naraki\Mail\Contracts\Listing as EmailListInterface;
use Naraki\Mail\Models\EmailList as EmailListModel;

/**
 * @method \Naraki\Mail\Models\EmailList createModel(array $attributes = [])
 */
class Listing extends EloquentProvider implements EmailListInterface
{
    protected $model = \Naraki\Mail\Models\EmailList::class;

    public function getList()
    {
        return EmailListModel::getList();
    }
}