<?php namespace Naraki\Mail\Providers;

use Naraki\Mail\Models\EmailList;
use Naraki\Mail\Models\EmailSubscriber as SubscriberModel;
use Naraki\Core\Models\Entity;
use Naraki\Core\Models\EntityType;
use Naraki\Sentry\Models\Person;
use Naraki\Core\EloquentProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Naraki\Mail\Contracts\Subscriber as SubscriberInterface;

/**
 * @method \Naraki\Mail\Models\EmailSubscriber createModel(array $attributes = [])
 */
class Subscriber extends EloquentProvider implements SubscriberInterface
{
    protected $model = \Naraki\Mail\Models\EmailSubscriber::class;

    /**
     * @param int $personID
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildAllUser($personID, $columns = ['*']): Builder
    {
        return $this->build()
            ->recipientEntityType()
            ->emailList()
            ->person()
            ->user($personID)
            ->select($columns);
    }

    /**
     * @param int $personID
     * @param array $savedList
     * @return boolean true if mailing lists were added
     */
    public function addUserToLists($personID, $savedList)
    {
        $currentUserLists = $this->buildAllUser(
            $personID,
            ['email_lists.email_list_id'])
            ->pluck('email_list_id')->toArray();

        $listsToRemove = array_diff($currentUserLists, $savedList);
        $listsToAdd = array_diff($savedList, $currentUserLists);
        if (empty($listsToAdd) && empty($listsToRemove)) {
            return false;
        }
        $targetID = EntityType::getEntityTypeID(Entity::PEOPLE, intval($personID));

        if (!is_null($targetID)) {
            if (!empty($listsToRemove)) {
                SubscriberModel::query()
                    ->where('email_subscriber_target_id', '=', $targetID)
                    ->whereIn('email_list_id', $listsToRemove)->delete();
            }

            if (!empty($listsToAdd)) {
                $subscriberDb = [];
                foreach ($listsToAdd as $listId) {
                    $subscriberDb[] = [
                        'email_subscriber_target_id' => $targetID,
                        'email_list_id' => $listId
                    ];
                }
                try {
                    SubscriberModel::insert($subscriberDb);
                    return true;
                } catch (QueryException $e) {
                    //Probably a unique index being triggered in case we subscribe the user
                    //to lists he's already in.
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * @param array $input
     * @param array $lists
     * @return array
     */
    public function addPersonToLists($input, $lists = []): ?array
    {
        if (empty($lists)) {
            $lists = EmailList::getDefaults();
        }

        if (!isset($input['email']) || empty($input['email'])) {
            return null;
        }

        try {
            $person = new Person([
                'full_name' => $input['full_name'],
                'email' => $input['email']
            ]);
            $person->save();
        } catch (\Illuminate\Database\QueryException $e) {
            $person = Person::buildByEmail($input['email'], ['person_id'])->first();
            if (is_null($person)) {
                return null;
            }
        }

        $result = $this->addUserToLists($person->getKey(), $lists);
        return $result ? $input : null;
    }

}