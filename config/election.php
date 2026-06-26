<?php

return [

    /*
    |--------------------------------------------------------------------------
    | EOI payment grace period
    |--------------------------------------------------------------------------
    |
    | Unpaid expression-of-interest applications older than this window are
    | auto-rejected and no longer hold an office applicant slot.
    |
    */
    'eoi_payment_grace_hours' => (int) env('EOI_PAYMENT_GRACE_HOURS', 48),

];
