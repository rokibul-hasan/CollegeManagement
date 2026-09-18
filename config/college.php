<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    |
    | The owner account. It always has every permission, is the only account
    | that can run migrations / clear caches from the System page, and cannot
    | be seen, edited or removed by other admins.
    |
    */

    'super_admin_email' => env('SUPER_ADMIN_EMAIL', 'rokibulhasan.356@gmail.com'),

    /*
    | Roles that ship with the application and cannot be deleted.
    */

    'system_roles' => ['super-admin', 'admin', 'editor', 'teacher', 'student'],

];
