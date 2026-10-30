<?php

return [
    /*
    | Duree de vie (jours) d'un jeton mobile sur les routes /mobile/v1/distribution/*.
    | Au-dela, l'agent doit se reconnecter. 0 = pas de limite.
    | Ne modifie pas les jetons des autres applications (SIM, comptable...).
    */
    'token_ttl_days' => (int) env('DISTRIBUTION_TOKEN_TTL_DAYS', 30),
];
