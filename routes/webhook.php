<?php

use Brucelwayne\Subscribe\Controllers\MailgunController;

\Illuminate\Support\Facades\Route::post('/webhook/mailgun', [MailgunController::class, 'webhook']);