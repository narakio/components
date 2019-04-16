<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Naraki\Core\Models\Entity;

class Entities extends Migration
{

    private $entityPrimaryKeyColumns = [];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->smallIncrements('entity_id');
            $table->string('entity_name', 75)->nullable();
        });

        Schema::create('entity_types', function (Blueprint $table) {
            $table->increments('entity_type_id');

            $table->unsignedSmallInteger('entity_id')->unsigned()->default(Entity::SYSTEM);
            $table->integer('entity_type_target_id')->unsigned()->comment('the ID in the entity\'s (users,products,etc.) table');

            $table->foreign('entity_id')
                ->references('entity_id')->on('entities');

            $table->unique(['entity_id', 'entity_type_target_id'], 'idx_entity_target');
            $table->unique(['entity_id', 'entity_type_id'], 'idx_entity_type');
        });

        Schema::create('email_recipient_types', function (Blueprint $table) {
            $table->increments('email_recipient_type_id');
            $table->string('email_recipient_type_name', 50);
        });
        \Naraki\Mail\Models\EmailRecipientType::insert([
            ['email_recipient_type_name' => 'all'],
        ]);

        Schema::create('emails', function (Blueprint $table) {
            $table->increments('email_id');
            $table->unsignedInteger('email_recipient_type_id')
                ->default(\Naraki\Mail\Models\EmailRecipientType::ALL);
            $table->text('email_content')->nullable();
            $table->text('email_sources')->nullable();
            $table->foreign('email_recipient_type_id')
                ->references('email_recipient_type_id')->on('email_recipient_types')
                ->onDelete('cascade');
        });

        \DB::beginTransaction();
        $this->addEntities();
        $this->entityTypes();
        $this->createEntities();
        $this->createTriggers();
        $this->createGroups();
        \DB::commit();
    }

    private static function createGroups()
    {
        $u = factory(Naraki\Sentry\Models\User::class)->create([
            'username' => 'root',
            'password' => bcrypt(config('auth.root_password')),
            'activated' => true,
            'user_id' => 1,
            'remember_token' => null,
        ]);
        factory(Naraki\Sentry\Models\Person::class)->create([
            'person_id' => 1,
            'email' => 'system@localhost.local',
            'first_name' => 'root',
            'last_name' => '',
            'user_id' => 1
        ]);

        (new \Naraki\Sentry\Models\Group)->insert([
            [
                'group_name' => 'root',
                'group_slug' => 'root',
                'group_id' => 1,
                'group_mask' => 1
            ],
            [
                'group_name' => 'Super Admins',
                'group_slug' => 'super-admins',
                'group_id' => 2,
                'group_mask' => 2
            ],
            [
                'group_name' => 'Admins',
                'group_slug' => 'admins',
                'group_id' => 3,
                'group_mask' => 2000
            ],
            [
                'group_name' => 'Users',
                'group_slug' => 'users',
                'group_id' => 4,
                'group_mask' => 5000
            ],
        ]);
        factory(Naraki\Sentry\Models\GroupMember::class)->create([
            "group_id" => 1,
            'user_id' => 1
        ]);
    }

    private function addEntities()
    {
        $entities = Entity::getConstants();
        foreach ($entities as $entityName => $key) {
            $this->entities[] = [
                'entity_id' => $key,
                'entity_name' => strtolower($entityName)
            ];
        }
        Entity::insert($this->entities);
    }

    private function entityTypes()
    {
        $entityTypes = [];

        //The system entity has an ID of one and doesn't match a table in the DB
        $entities = \DB::select('
            SELECT entity_id,entity_name AS name
            FROM entities
            WHERE entity_name not in ("system","media") ORDER BY entity_id ASC');
        $k = 1;

        //We keep track of each entity's ID
        foreach ($entities as $entity) {
            $primaryKey = Entity::createModel($entity->entity_id)->getKeyName();
            if ($primaryKey == 'id') {
                continue;
            }
            $this->entityPrimaryKeyColumns[$entity->name] = $primaryKey;
            $ids = \DB::select(sprintf('
            SELECT %s
            FROM %s', $primaryKey, $entity->name));

            $entityID = constant(
                sprintf('%s::%s', Entity::class, strtoupper($entity->name))
            );
            $this->entityIDtoEntityTypeIDTable[$entityID] = [];
        }
    }

    private function createTriggers()
    {
        $entities = $this->entities;
        $db = app()->make(\Naraki\Core\Contracts\RawQueries::class);
        foreach ($entities as $entity) {
            if (!isset($this->entityPrimaryKeyColumns[$entity['entity_name']]) || $entity['entity_name'] == 'system') {
                continue;
            }
            if ($entity['entity_name'] !== 'users') {
                $db->triggerCreateEntityType(
                    $entity['entity_name'],
                    $this->entityPrimaryKeyColumns[$entity['entity_name']]
                );
            }
            $db->triggerDeleteEntityType(
                $entity['entity_name'],
                $this->entityPrimaryKeyColumns[$entity['entity_name']],
                $entity['entity_id']
            );
        }
        $db->triggerCreateEntityTypeUsers();
        $db->triggerUserFullName();
        $db->triggerUserUpdateActivated();

    }

    private function createEntities()
    {
        $entity = \Naraki\Core\Models\EntityType::create(['entity_type_target_id' => 0]);
        $entity->save();
        $pk = $entity->getKeyName();
        $entity->setAttribute($pk, 0);
        $entity->save();

        $entity = \Naraki\Sentry\Models\User::create([]);
        $entity->save();
        $pk = $entity->getKeyName();
        $entity->setAttribute($pk, 0);
        $entity->save();
        (new \Naraki\Core\Models\EntityType)->insert([
            'entity_id' => Entity::USERS,
            'entity_type_target_id' => 0,
            'entity_type_id' => 1
        ]);
        $entity = \Naraki\Sentry\Models\Person::create([$pk => 0]);
        $entity->save();
        $entity->setAttribute($entity->getKeyName(), 0);
        $entity->save();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
