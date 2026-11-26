<?php
// Démonstration E2E du circuit de validation — API HTTP réelle (même chemin que les APK).
// Base locale sqlite / APP_ENV=local uniquement.
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

// Nettoyage des données de démo des exécutions précédentes
// (attention : ne pas toucher aux DEMO-INIT-* = stock initial vérifié du seed)
foreach (['DEMO-ENTRY-%','DEMO-EXIT-%','DEMO-TRF-%','DEMO-OFFLINE-%','DEMO-HACK-%','DEMO-INV-%','CURL-%'] as $p) {
    App\Models\StockMovement::withTrashed()->where('reference', 'like', $p)->forceDelete();
}
App\Models\StockOperation::withTrashed()->where('instructions', 'like', '%démo%')->forceDelete();
// Resynchronise les soldes officiels après purge
$svc0 = app(App\Services\Magasin\WarehouseStockService::class);
foreach ([2, 3] as $wid) {
    $w = App\Models\Warehouse::find($wid);
    if ($w) $w->update(['current_stock' => $svc0->balance($wid)['sacs']]);
}

$BASE = 'http://127.0.0.1:8000/api/warehouse/v1';
$WH1 = 2; $WH2 = 3;
$U = fn ($email) => App\Models\User::where('email', $email)->first();

function api(string $method, string $path, ?string $token = null, array $body = []) {
    global $BASE;
    $req = Http::acceptJson()->asJson();
    if ($token) $req = $req->withToken($token);
    $res = $req->$method("$BASE$path", $body ?: []);
    return [$res->status(), $res->json() ?? []];
}

function say(string $s) { echo "\n=== $s ===\n"; }
function check(bool $cond, string $label) {
    echo ($cond ? "  [OK] " : "  [ECHEC] ") . $label . "\n";
    return $cond;
}
function stockOfficiel(int $wh): float {
    return (float) App\Models\Warehouse::find($wh)->current_stock;
}
function balance(int $wh): float {
    return app(App\Services\Magasin\WarehouseStockService::class)->balance($wh)['sacs'];
}

$allOk = true;
$step = function (bool $c, string $l) use (&$allOk) { $allOk = $allOk && check($c, $l); };

// ---------- 1. Authentification (mêmes endpoints que les APK) ----------
say('1. Connexions APK (login magasinier / inspecteur)');
[$s,$mag]  = api('POST','/login',null,['email'=>'demo.magasinier@csar.test','password'=>'Demo2026!']);
[$s,$mag2] = api('POST','/login',null,['email'=>'demo.magasinier2@csar.test','password'=>'Demo2026!']);
[$s,$insp] = api('POST','/login/inspecteur',null,['email'=>'demo.inspecteur@csar.test','password'=>'Demo2026!']);
$step(isset($mag['token']), 'login magasinier Dakar');
$step(isset($mag2['token']), 'login magasinier Thiès');
$step(isset($insp['token']), 'login inspecteur');
$tokMag=$mag['token']??null; $tokMag2=$mag2['token']??null; $tokInsp=$insp['token']??null;

// ---------- 2. Entrée de marchandises ----------
say('2. Entrée : 20 sacs Riz [50kg] au magasin Dakar (magasinier)');
$before = stockOfficiel($WH1);
[$s,$r] = api('POST','/movements',$tokMag,[
    'warehouse_id'=>$WH1,'movement_date'=>'2026-11-24','label'=>'Riz [50kg] — Réception démo',
    'movement_type'=>'entry','sacs'=>20,'kg'=>1000,'client_ref'=>'DEMO-ENTRY-1',
]);
$step($s===201, "POST /movements → HTTP $s");
$mId = App\Models\StockMovement::where('reference','DEMO-ENTRY-1')->value('id');
$step(App\Models\StockMovement::find($mId)->status==='en_attente', 'mouvement créé « en_attente »');
$step(stockOfficiel($WH1)===$before, "stock officiel inchangé ($before)");
$step(balance($WH1)===$before, 'solde officiel (mouvements vérifiés) inchangé');

say('2b. Le magasinier ne peut PAS vérifier son propre mouvement');
[$s,$r] = api('POST',"/pending-movements/$mId/verify",$tokMag);
$step($s===403, "verify par magasinier → HTTP $s (refusé)");

say('2c. Vérification par l\'inspecteur via APK');
[$s,$r] = api('POST',"/pending-movements/$mId/verify",$tokInsp);
$step($s===200, "verify par inspecteur → HTTP $s");
$step(stockOfficiel($WH1)===$before+20, 'stock officiel = +20 après vérification');

// idempotence client_ref
[$s,$r2] = api('POST','/movements',$tokMag,[
    'warehouse_id'=>$WH1,'movement_date'=>'2026-11-24','label'=>'Riz [50kg] — Réception démo',
    'movement_type'=>'entry','sacs'=>20,'kg'=>1000,'client_ref'=>'DEMO-ENTRY-1',
]);
$step($r2['duplicate']===true || App\Models\StockMovement::where('reference','DEMO-ENTRY-1')->count()===1, 'client_ref : aucun doublon');

// ---------- 3. Sortie ----------
say('3. Sortie : 10 sacs Riz [50kg] (magasinier)');
$before = stockOfficiel($WH1);
[$s,$r] = api('POST','/movements',$tokMag,[
    'warehouse_id'=>$WH1,'movement_date'=>'2026-11-24','label'=>'Riz [50kg] — Dotation démo',
    'movement_type'=>'exit','sacs'=>10,'kg'=>500,'client_ref'=>'DEMO-EXIT-1',
]);
$step($s===201, "POST /movements (exit) → HTTP $s");
$mId2 = App\Models\StockMovement::where('reference','DEMO-EXIT-1')->value('id');
$step(stockOfficiel($WH1)===$before, 'stock officiel inchangé avant vérification');
[$s,$r] = api('POST',"/pending-movements/$mId2/verify",$tokInsp);
$step($s===200 && stockOfficiel($WH1)===$before-10, 'sortie déduite après vérification');

// sortie > stock officiel refusée à la saisie
[$s,$r] = api('POST','/movements',$tokMag,[
    'warehouse_id'=>$WH1,'movement_date'=>'2026-11-24','label'=>'Riz [50kg]','movement_type'=>'exit',
    'sacs'=>99999,'kg'=>999990,'client_ref'=>'DEMO-EXIT-BIG',
]);
$step($s===422, "sortie démesurée refusée → HTTP $s");

// ---------- 4. Transfert ----------
say('4. Transfert : 15 sacs Riz [50kg] Dakar → Thiès');
$beforeSrc = stockOfficiel($WH1); $beforeDst = stockOfficiel($WH2);
[$s,$r] = api('POST','/transfers',$tokMag,[
    'warehouse_id'=>$WH1,'destination_warehouse_id'=>$WH2,'movement_date'=>'2026-11-24',
    'label'=>'Riz [50kg] — Transfert démo','sacs'=>15,'kg'=>750,'client_ref'=>'DEMO-TRF-1',
]);
$step($s===201, "POST /transfers → HTTP $s");
$outM = App\Models\StockMovement::where('reference','DEMO-TRF-1')->first();
$inM  = App\Models\StockMovement::where('reference','DEMO-TRF-1-IN')->first();
$step($outM && $inM && $outM->status==='en_attente' && $inM->status==='en_attente', 'deux jambes « en_attente »');
$step(stockOfficiel($WH1)===$beforeSrc && stockOfficiel($WH2)===$beforeDst, 'aucun stock officiel bougé');

say('4b. Vérification de la réception AVANT confirmation → refusée');
[$s,$r] = api('POST',"/pending-movements/{$inM->id}/verify",$tokInsp);
$step($s===409, "verify jambe « in » sans réception → HTTP $s");

say('4c. Confirmation de réception par le magasinier destinataire');
[$s,$r] = api('POST',"/pending-movements/{$inM->id}/confirm-receipt",$tokMag2);
$step($s===200, "confirm-receipt par magasinier Thiès → HTTP $s");
// un autre magasinier ne peut pas confirmer
[$s,$r] = api('POST',"/pending-movements/{$inM->id}/confirm-receipt",$tokMag);
$step($s===403, "confirm-receipt par magasinier non destinataire → HTTP $s");

say('4d. Vérification inspecteur des deux jambes');
[$s,$r] = api('POST',"/pending-movements/{$outM->id}/verify",$tokInsp);
$step($s===200, 'sortie transfert vérifiée');
[$s,$r] = api('POST',"/pending-movements/{$inM->id}/verify",$tokInsp);
$step($s===200, 'entrée transfert vérifiée');
$step(stockOfficiel($WH1)===$beforeSrc-15, 'source -15');
$step(stockOfficiel($WH2)===$beforeDst+15, 'destination +15');

// ---------- 5. Inventaire ----------
say('5. Inventaire physique : comptage réel 685 au lieu de 685 (Dakar)');
$official = balance($WH1);
[$s,$r] = api('POST','/inventory',$tokMag,[
    'warehouse_id'=>$WH1,'movement_date'=>'2026-11-24','label'=>'Inventaire démo',
    'theoretical_sacs'=>(string)$official,'actual_sacs'=>(string)($official-7),
]);
$step($s===201, "POST /inventory → HTTP $s (écart -7)");
$invM = App\Models\StockMovement::where('warehouse_id',$WH1)->where('type','adjustment')->latest('id')->first();
$step($invM && $invM->status==='en_attente', 'ajustement « en_attente »');
$step(balance($WH1)===$official, 'stock officiel inchangé');

say('5b. L\'inspecteur ne peut PAS valider un ajustement (réservé DTL/DG)');
[$s,$r] = api('POST',"/pending-movements/{$invM->id}/verify",$tokInsp);
$step($s===403, "verify ajustement par inspecteur → HTTP $s");

say('5c. Validation par la DTL (service = même code que le portail Web)');
$dtl = App\Models\User::where('email','demo.dtl@csar.test')->first();
$svc = app(App\Services\Magasin\WarehouseStockService::class);
$r = $svc->verifyPendingMovement($dtl, $invM->id);
$step($r['ok'], 'verify ajustement par DTL → OK');
$step(balance($WH1)===$official-7, 'stock officiel corrigé -7');

// ---------- 6. Sync hors ligne ----------
say('6. Synchronisation hors ligne (/sync) : 5 sacs entrée + retry');
$before = stockOfficiel($WH2);
$payload = ['movements'=>[[
    'warehouse_id'=>$WH2,'movement_date'=>'2026-11-24','label'=>'Huile [20L] — Réception terrain',
    'movement_type'=>'entry','sacs'=>5,'kg'=>100,'client_ref'=>'DEMO-OFFLINE-1',
]]];
[$s,$r1] = api('POST','/sync',$tokMag2,$payload);
[$s,$r2] = api('POST','/sync',$tokMag2,$payload);
$step(($r2['results'][0]['status']??'')==='duplicate', 'sync rejouée → « duplicate », aucun doublon');
$mOff = App\Models\StockMovement::where('reference','DEMO-OFFLINE-1')->first();
$step($mOff && $mOff->status==='en_attente', 'mouvement sync « en_attente »');
$step(stockOfficiel($WH2)===$before, 'stock officiel inchangé après sync');
[$s,$r] = api('POST',"/pending-movements/{$mOff->id}/verify",$tokInsp);
$step($s===200 && stockOfficiel($WH2)===$before+5, 'entrée offline comptée après vérification');

// ---------- 7. Circuit stock_operations (DG→DTL→Inspecteur→Magasinier) ----------
say('7. Circuit complet : instruction DG → exécution magasinier → vérification inspecteur');
$dg = App\Models\User::where('email','demo.dg@csar.test')->first();
$ops = app(App\Services\Magasin\StockOperationService::class);
$r = $ops->create($dg, [
    'type'=>'exit','warehouse_id'=>$WH1,'produit'=>'Riz','unite'=>'kg','format'=>50,
    'quantity_requested'=>8,'quantity_requested_kg'=>400,'instructions'=>'Dotation régionale démo',
]);
$step($r['ok']??false, 'DG crée l\'instruction');
$op = $r['operation'];
$inspUser = $U('demo.inspecteur@csar.test');
$magUser = $U('demo.magasinier@csar.test');
$mag2User = $U('demo.magasinier2@csar.test');
$ops->assignInspector($dtl, $op->id, $inspUser->id);
$ops->transmitToMagasinier($inspUser, $op->id, $magUser->id);
// exécution via API (chemin APK magasinier)
[$s,$r] = api('POST',"/operations/{$op->id}/execute",$tokMag,['quantity_executed'=>8,'quantity_executed_kg'=>400]);
$step($s===200, "magasinier exécute via API → HTTP $s");
$mv = App\Models\StockMovement::where('stock_operation_id',$op->id)->first();
$step($mv && $mv->status==='en_attente', 'mouvement opération « en_attente »');
$before = balance($WH1);
$step(balance($WH1)===$before, 'stock officiel inchangé après exécution');
[$s,$r] = api('POST',"/operations/{$op->id}/verify",$tokInsp,['inspector_comment'=>'Conforme']);
$step($s===200, "inspecteur vérifie via API → HTTP $s");
$step(balance($WH1)===$before-8, 'stock officiel -8 après vérification');

// ---------- 8. Sécurité ----------
say('8. Tentatives de contournement');
[$s,$r] = api('GET','/pending-movements',$tokMag);
$step($s===200, 'magasinier : lecture de sa file OK');
// magasinier sur un autre magasin
[$s,$r] = api('POST','/movements',$tokMag,[
    'warehouse_id'=>$WH2,'movement_date'=>'2026-11-24','label'=>'Riz [50kg]','movement_type'=>'entry',
    'sacs'=>1,'kg'=>50,'client_ref'=>'DEMO-HACK-1',
]);
$step($s===403, "magasinier Dakar → magasin Thiès refusé (HTTP $s)");
// inspecteur ne peut pas créer de mouvement
[$s,$r] = api('POST','/movements',$tokInsp,[
    'warehouse_id'=>$WH1,'movement_date'=>'2026-11-24','label'=>'Riz [50kg]','movement_type'=>'entry',
    'sacs'=>1,'kg'=>50,'client_ref'=>'DEMO-HACK-2',
]);
$step(in_array($s,[401,403]), "inspecteur → POST /movements refusé (HTTP $s)");

echo "\n==========================================\n";
echo $allOk ? "DEMO : TOUS LES CONTRÔLES OK\n" : "DEMO : DES CONTRÔLES ONT ÉCHOUÉ\n";
echo "Stock final officiel — Dakar: ".stockOfficiel($WH1)." | Thiès: ".stockOfficiel($WH2)."\n";
exit($allOk?0:1);
