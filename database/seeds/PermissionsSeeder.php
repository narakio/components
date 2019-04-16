<?php

use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    use \Illuminate\Foundation\Bus\DispatchesJobs;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        (new \Naraki\Permission\Models\Permission())->insert([
            [
                'entity_type_id' => 8,
                'entity_id' => \Naraki\Core\Models\Entity::USERS,
                'permission_mask' => 0b1101,
            ],
            [
                'entity_type_id' => 100,
                'entity_id' => \Naraki\Core\Models\Entity::USERS,
                'permission_mask' => 0b111,
            ],
            [
                'entity_type_id' => 100,
                'entity_id' => \Naraki\Core\Models\Entity::GROUPS,
                'permission_mask' => 0b1101,
            ],
            [
                'entity_type_id' => 100,
                'entity_id' => \Naraki\Core\Models\Entity::BLOG_POSTS,
                'permission_mask' => 0b1010,
            ],
            [
                'entity_type_id' => 101,
                'entity_id' => \Naraki\Core\Models\Entity::BLOG_POSTS,
                'permission_mask' => 0b1010,
            ],
        ]);
        $this->dispatch(new \Naraki\Permission\Jobs\Update);
    }
}
