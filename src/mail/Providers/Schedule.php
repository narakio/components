<?php namespace Naraki\Mail\Providers;

use Naraki\Mail\Models\Email as EmailModel;
use Naraki\Mail\Models\EmailSchedule as EmailScheduleModel;
use Naraki\Core\Models\Entity;
use Naraki\Core\Models\EntityType;
use Naraki\Core\EloquentProvider;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Naraki\Mail\Contracts\Schedule as ScheduleInterface;

/**
 * @method \Naraki\Mail\Models\EmailSchedule createModel(array $attributes = [])
 */
class Schedule extends EloquentProvider implements ScheduleInterface
{
    protected $model = \Naraki\Mail\Models\EmailSchedule::class;

    /**
     * @param int $entityID
     * @param int $targetID
     *
     * @return bool
     */
    public function hasScheduledEmail($entityID, $targetID)
    {
        $model = $this->createModel();
        $existingSchedule = $model->newQuery()
            ->emailEvent($entityID)
            ->sourceEntityType($targetID)
            ->select([$model->getKeyName()])
            ->first();

        return (!is_null($existingSchedule));
    }

    /**
     * @param array $input
     *
     * @return static
     */
    public function addScheduleWithContent($input)
    {
        $dataSchedule = [
            'email_list_id' => $input['email_list_id'],
            'email_schedule_name' => $input['email_schedule_name'],
        ];
        if (empty($input['email_schedule_send_at'])) {
            $dataSchedule['email_schedule_send_at'] = Carbon::now()->addDay()->toDateString();
        } else {
            $dataSchedule['email_schedule_send_at'] = Carbon::createFromFormat('d/m/Y',
                $input['email_schedule_send_at'])->toDateString();
        }

        $emailContent = EmailModel::create([
            'email_recipient_type_id' => $input['email_recipient_type'],
            'email_content' => $input['email_content'],
            'email_content_sources' => implode(',', $input['entity_ids']),
        ]);

        $dataSchedule['email_schedule_source_id'] = EntityType::getEntityTypeID(
            Entity::EMAILS, $emailContent->getKey()
        );

        return EmailScheduleModel::create($dataSchedule);
    }

    /**
     * @param int $id
     * @param array $data
     *
     * @return null
     */
    public function updateOne($id, $data)
    {
        $model = $this->createModel();
        if (!empty($data['email_schedule_send_at'])) {
            $date = Carbon::createFromFormat('d/m/Y', $data['email_schedule_send_at'])->setTime(0, 0, 0);
            $now = Carbon::now()->setTime(0, 0, 0);
            if ($date->gt($now)) {
                return $model->where($model->getKeyName(),
                    $id)->update(['email_schedule_send_at' => $date->toDateString()]);
            }
        } else {
            $model->where($model->getKeyName(), $id)->delete();
        }

        return null;
    }

    /**
     * @param int $entityTypeID
     * @param int $entityID
     * @param string $targetName
     * @param string $dateSent
     * @param int $emailEvent
     *
     * @throws \Exception
     */
    public function updateEntitySchedule(
        $entityTypeID,
        $entityID,
        $targetName,
        $dateSent,
        $emailEvent
    ) {
        //Emails are sent the next day through a cronjob that calls the SendMail command
        if (is_null($dateSent)) {
            $dateSent = Carbon::create()->addDay()->toDateString();
        }
        $model = $this->createModel();
        $existingSchedule = $model->newQuery()
            ->where('email_schedule_source_id', $entityTypeID)
            ->select([$model->getKeyName()])
            ->first();

        if (!is_null($existingSchedule)) {
            $existingSchedule->setAttribute('email_schedule_send_at', $dateSent);
            $existingSchedule->save();
        } else {
            EmailScheduleModel::create([
                'email_schedule_name' => sprintf('%s - %s',
                    trans(sprintf('common.%s', Entity::getConstantName($entityID))),
                    $targetName),
                'email_schedule_source_id' => $entityTypeID,
                'email_list_id' => $emailEvent,
                'email_schedule_send_at' => $dateSent,
            ]);
            EmailContent::insert([
                'email_content_sources' => $entityTypeID,
            ]);
        }
    }

    /**
     * @param int $id
     * @param array $columns
     *
     * @return mixed
     */
    public function getOne($id, $columns = ['*'])
    {
        $model = $this->createModel();
        $schedule = $model->newQuery()
            ->where($model->getQualifiedKeyName(), $id)
            ->emailEvent()
            ->sourceEntityType()
            ->select([
                'entity_types.entity_id',
                'entity_type_target_id',
                'email_schedule_id',
                'email_schedules.email_list_id',
                'email_schedule_name'
            ])
            ->first();
        $targetID = $schedule->getAttribute('entity_type_target_id');

        return new Collection([
            'content' => $this->content->yieldEmailContent(
                $targetID,
                $schedule->getAttribute('email_list_id')
            ),
            'schedule' => (object)$schedule->toArray()
        ]);
    }

    /**
     * @param int $entityTypeID
     */
    public function destroyEntitySchedule($entityTypeID)
    {
        EmailScheduleModel::query()
            ->where('email_schedule_source_id', $entityTypeID)
            ->delete();
    }

    /**
     * @return array|null
     */
    public function scheduledEmailToday()
    {
        $model = $this->createModel();
        $todayDate = Carbon::create()->toDateString();
        $existingSchedules = $model->newQuery()
            ->where('email_schedule_send_at', $todayDate)
            ->emailEvent()
            ->sourceEntityType()
            ->select([
                'entity_types.entity_id',
                'entity_type_target_id',
                'email_schedule_id',
                'email_schedules.email_list_id',
                'email_schedule_name'
            ])
            ->get();
        if (!$existingSchedules->isEmpty()) {
            $content = $websites = [];

            foreach ($existingSchedules as $schedule) {
                $targetID = $schedule->getAttribute('entity_type_target_id');
                $tmp = $this->content->yieldEmailContent($targetID,
                    $schedule->getAttribute('email_list_id'));
                if (!empty($tmp)) {
                    $tmp->put('email_schedule_id', $schedule->getAttribute('email_schedule_id'));
                    $tmp->put('email_list_id', $schedule->getAttribute('email_list_id'));
                    $tmp->put('email_schedule_name', $schedule->getAttribute('email_schedule_name'));
                    $content[$schedule->getAttribute('email_list_id')][$targetID] = $tmp;
                }
            }
        } else {
            return null;
        }

        return $content;
    }

}