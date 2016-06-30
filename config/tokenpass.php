<?php

return array(
    'sig_verify_prefix' =>  env('TOKENPASS_SIG_VERIFY_PREFIX', 'TOKENPASS'),
    'crypto_verify_code_expire' => 3600,
    'instant_verify_code_expire' => 600,

);
