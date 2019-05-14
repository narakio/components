<?php

return [
    'breadcrumb' => [
        'admin-users-index' => 'Users',
        'admin-groups-index' => 'Groups',
        'admin-users-edit' => 'Edit',
        'admin-users-add' => 'Add',
        'admin-groups-edit' => 'Edit',
        'admin-groups-add' => 'New Group',
        'admin-groups-members' => 'Edit Members',
    ],
    'db' => [
        'first_name' => 'First name',
        'full_name' => 'Full name',
        'group_mask' => 'Group mask',
        'group_name' => 'Group name',
        'groups' => 'Group|Groups',
        'last_name' => 'Last name',
        'member_count' => 'Number of members',
        'new_email' => 'New e-mail',
        'email' => 'E-mail',
        'new_group_name' => 'New group name',
        'new_username' => 'New username',
        'user_created_at' => 'Registration date',
        'username' => 'Username',
        'users' => 'User|Users',
        'system' => 'System'
    ],
    'db_raw' => [
        'full_name' => 'full_name',
        'username' => 'username',
        'email' => 'email',
        'group_name' => 'group_name',
        'created_at' => 'created_at',
        'created_ago' => 'created_at'
    ],
    'db_raw_inv' => [
        'full_name' => 'full_name',
        'username' => 'username',
        'email' => 'email',
        'group_name' => 'group_name',
        'created_at' => 'created_at',
    ],
    'filter_labels' => [
        'users_group' => 'Group:',
        'users_name' => 'Full name:',
        'media_title' => 'Media title:',
        'users_created' => 'Registration period:',
        'created_today' => 'Registered today',
        'created_week' => 'Less than a week ago',
        'created_month' => 'Less than a month ago',
        'created_year' => 'Less than a year ago',
    ],
    'filters_inv' => [
        'registration' => 'createdAt',
        'group' => 'group',
        'name' => 'fullName',
        'sortBy' => 'sortBy',
        'title' => 'title',
        'order' => 'order',
        'fullName' => 'name',
        'createdAt' => 'created',
    ],
    'form' => [
        'description' => [
            'username' => 'The user\'s shorthand name. Limited to 25 characters, and may only contain letters and underscores.',
            'username_help' => 'Limited to 25 characters, and may only contain letters and underscores.',
            'first_name' => 'The user\'s first (given) name.',
            'last_name' => 'The user\'s last (family) name.',
            'new_email' => '"{0}" is the current e-mail address.',
            'new_username' => '"{0}" is the current username.',
            'group_name' => 'The group name.',
            'new_group_name' => '"{0}" is the current group name.',
            'group_mask' => 'Determines the group\'s position in its hierarchy. The lower the mask, the higher the group status.',
        ],
    ],
    'groups' => [
        'add_group' => 'Add group',
        'info1' => 'Permissions for all members of the group are defined here.',
        'info2' => 'Individual permissions can also be set at the user level,
            in which case user permissions will override permissions set here.'
    ],
    'members' => [
        'member_search' => 'Type user full name here, i.e "Jane Doe"',
        'group_name' => 'Group:',
        'edit_preview' => 'Preview',
        'no_changes' => 'No changes so far.',
        'add_members' => 'Add members',
        'remove_members' => 'Remove members',
        'user_add_tag' => 'The following users will be added:',
        'user_no_add' => 'No added members.',
        'user_remove_tag' => 'The following users will be removed:',
        'user_no_remove' => 'No removed members.',
        'user_none' => 'There are no members in this group.',
        'current_members' => 'The following users are members of this group:',
    ],
    'modal' => [
        'user_delete' => [
            'h' => 'Confirm user deletion',
            't' => 'Do you really want to delete user {name}?|Do you really want to delete those {number} users?'
        ],
        'group_delete' => [
            'h' => 'Confirm group deletion',
            't' => 'Do you really want to delete group {name}?'
        ],
    ],
    'users' => [
        'warning1' => 'Setting individual permissions for this user will override permissions set on groups of which the user is a member.',
        'warning2' => 'We recommend setting permissions on groups instead, 
            and use individual user permissions to handle exceptions.',
        'filter_full_name' => 'Filter by full name',
        'filter_group' => 'Filter by group',
        'filter_created_at' => 'Filter by registration date',
        'new_user' => 'New User'
    ],
];
