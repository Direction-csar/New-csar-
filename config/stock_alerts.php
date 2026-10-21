<?php

return [
    // Jours avant péremption à partir desquels un lot est signalé.
    'expiring_days' => (int) env('STOCK_ALERT_EXPIRING_DAYS', 30),

    // En deçà, la péremption proche devient critique.
    'expiring_critical_days' => (int) env('STOCK_ALERT_EXPIRING_CRITICAL_DAYS', 7),

    // Stock critique : priorité critique si quantité <= ratio x seuil minimal.
    'low_stock_critical_ratio' => (float) env('STOCK_ALERT_LOW_CRITICAL_RATIO', 0.5),

    // Rôles voyant tous les magasins ; les autres sont limités à users.warehouse_id.
    'global_roles' => ['admin', 'super_admin', 'dg', 'directeur_general', 'signataire', 'scanneur'],
];
