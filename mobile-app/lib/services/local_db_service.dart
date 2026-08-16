import 'dart:convert';
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';

class LocalDbService {
  static Database? _db;

  static Future<Database> get database async {
    if (_db != null) return _db!;
    _db = await _initDb();
    return _db!;
  }

  static Future<Database> _initDb() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'csar_collecte.db');
    return openDatabase(
      path,
      version: 1,
      onCreate: (db, version) async {
        await db.execute('''
          CREATE TABLE pending_collections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            payload TEXT NOT NULL,
            created_at TEXT NOT NULL
          )
        ''');
      },
    );
  }

  static Future<int> savePendingCollection(Map<String, dynamic> data) async {
    final db = await database;
    return db.insert('pending_collections', {
      'payload': jsonEncode(data),
      'created_at': DateTime.now().toIso8601String(),
    });
  }

  static Future<List<Map<String, dynamic>>> getPendingCollections() async {
    final db = await database;
    final rows = await db.query('pending_collections', orderBy: 'id ASC');
    return rows
        .map((r) => {
              'id': r['id'] as int,
              'payload': jsonDecode(r['payload'] as String) as Map<String, dynamic>,
              'created_at': r['created_at'],
            })
        .toList();
  }

  static Future<void> deletePendingCollection(int id) async {
    final db = await database;
    await db.delete('pending_collections', where: 'id = ?', whereArgs: [id]);
  }

  static Future<int> countPending() async {
    final db = await database;
    final result = await db.rawQuery('SELECT COUNT(*) as count FROM pending_collections');
    return Sqflite.firstIntValue(result) ?? 0;
  }
}
