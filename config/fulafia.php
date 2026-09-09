<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FULAFIA student directory API
    |--------------------------------------------------------------------------
    |
    | Used for 2026+ alumni self-enrollment by matriculation number.
    | This is a consumed university directory API — it does not log users in.
    |
    */

    'student_url' => env('FULAFIA_STUDENT_URL', 'https://api.fulafia.edu.ng/api/v1/request/student'),

    'identity' => env('FULAFIA_IDENTITY', 'studenthub'),

    'secret' => env('FULAFIA_SECRET'),

    /*
    | App code sent with each lookup. UG is fixed for undergraduate self-enrollment.
    */
    'app' => env('FULAFIA_APP', 'UG'),

    'timeout' => (int) env('FULAFIA_TIMEOUT', 60),

    'connect_timeout' => (int) env('FULAFIA_CONNECT_TIMEOUT', 20),

    /*
    | Set false only for local TLS troubleshooting. Prefer true in production.
    */
    'verify_ssl' => filter_var(env('FULAFIA_HTTP_VERIFY_SSL', false), FILTER_VALIDATE_BOOLEAN),

    /*
    | Graduation year assigned to self-enrolled alumni when the directory
    | does not provide one. Defaults to the current calendar year.
    */
    'self_enrollment_graduation_year' => (int) env('FULAFIA_SELF_ENROLLMENT_GRADUATION_YEAR', date('Y')),

    /*
    | Default alumni category slug for UG API self-enrollment.
    */
    'self_enrollment_category_slug' => 'undergraduate-full-time',

];
