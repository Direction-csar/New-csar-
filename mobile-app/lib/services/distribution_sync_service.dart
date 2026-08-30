import 'distribution_api_service.dart';
import 'local_db_service.dart';

class DistributionSyncResult {
  final int synced;
  final int failed;
  final int remaining;
  DistributionSyncResult({required this.synced, required this.failed, required this.remaining});
}

class DistributionSyncService {
  static Future<DistributionSyncResult> syncPendingBeneficiaires(String token) async {
    final pending = await LocalDbService.getPendingBeneficiaires();
    int synced = 0;
    int failed = 0;

    for (final item in pending) {
      try {
        final res = await DistributionApiService.storeBeneficiaire(
          token,
          item['payload'] as Map<String, dynamic>,
        );
        if (res['success'] == true) {
          await LocalDbService.deletePendingBeneficiaire(item['id'] as int);
          synced++;
        } else {
          failed++;
        }
      } catch (_) {
        // Probablement toujours hors-ligne : on arrête et on réessaiera plus tard
        break;
      }
    }

    final remaining = await LocalDbService.countPendingBeneficiaires();
    return DistributionSyncResult(synced: synced, failed: failed, remaining: remaining);
  }
}
