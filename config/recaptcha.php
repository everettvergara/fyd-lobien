<?php

$siteKey = env('RECAPTCHA_SITE_KEY');
$secretKey = env('RECAPTCHA_SECRET_KEY');

return [

    'site_key' => $siteKey,

    'secret_key' => $secretKey,

    'score_threshold' => (float) env('RECAPTCHA_SCORE_THRESHOLD', 0.5),

    'enabled' => filled($siteKey) && filled($secretKey),

];
