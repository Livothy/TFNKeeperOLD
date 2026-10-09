<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Pages Configuration
    |--------------------------------------------------------------------------
    |
    | This file lets you set various settings to control user pages
    | behaviour across your site.
    |
    */

    // Maximum number of personal pages a regular user can create
    'user_page_limit' => 1,

    // Maximum number of personal pages a staff rank user can create
    'staff_page_limit' => 1,

    // Allow commenting; this simply allows it as an option,
    // users must *voluntarily* toggle comments on for their page(s)
    'allow_comments' => [
        'enabled'     => 1,
        'can_delete'  => 1, // Can users delete comments off their own user pages?
    ],

    // Ability to delete entire page; note these use softDeletes by
    // default in case of accidents, but you can toggle forceDelete here
    'allow_deletion' => [
        'enabled'      => 1,
        'force_delete' => 0, // Use forceDelete? This fully deletes the page from the DB
    ],

];
