import 'api_service.dart';
import 'local_db_service.dart';

class SyncResult {
  final int synced;
  final int failed;
  final int remaining;
  SyncResult({required this.synced, required this.failed, required this.remaining});
}

class SyncService {
  static Future<SyncResult> syncPendingBeneficiaires(String token) async {
    final pending = await LocalDbService.getPendingBeneficiaires();
    int synced = 0;
    int failed = 0;

    for (final item in pending) {
      try {
        final payload = Map<String, dynamic>.from(item['payload'] as Map<String, dynamic>);
        final res = await ApiService.storeBeneficiaire(token, payload);
        if (res['success'] == true) {
          await LocalDbService.deletePendingBeneficiaire(item['id'] as int);
          synced++;
        } else {
          failed++;
        }
      } catch (_) {
        break;
      }
    }

    final remaining = await LocalDbService.countPendingBeneficiaires();
    return SyncResult(synced: synced, failed: failed, remaining: remaining);
  }
}
