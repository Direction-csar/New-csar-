import 'api_service.dart';
import 'local_db_service.dart';

class SyncResult {
  final int synced;
  final int failed;
  final int remaining;
  SyncResult({required this.synced, required this.failed, required this.remaining});
}

class SyncService {
  static Future<SyncResult> syncPendingCollections(String token) async {
    final pending = await LocalDbService.getPendingCollections();
    int synced = 0;
    int failed = 0;

    for (final item in pending) {
      try {
        final res = await ApiService.submitCollection(
          token,
          item['payload'] as Map<String, dynamic>,
        );
        if (res['success'] == true) {
          await LocalDbService.deletePendingCollection(item['id'] as int);
          synced++;
        } else {
          failed++;
        }
      } catch (_) {
        // Probablement toujours hors-ligne : on arrête et on réessaiera plus tard
        break;
      }
    }

    final remaining = await LocalDbService.countPending();
    return SyncResult(synced: synced, failed: failed, remaining: remaining);
  }
}
