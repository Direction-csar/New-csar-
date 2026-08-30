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
    final path = join(dbPath, 'csar_distribution.db');
    return openDatabase(
      path,
      version: 1,
      onCreate: (db, version) async {
        await db.execute('''
          CREATE TABLE pending_beneficiaires (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            payload TEXT NOT NULL,
            created_at TEXT NOT NULL
          )
        ''');
      },
    );
  }

  static Future<int> savePendingBeneficiaire(Map<String, dynamic> data) async {
    final db = await database;
    return db.insert('pending_beneficiaires', {
      'payload': jsonEncode(data),
      'created_at': DateTime.now().toIso8601String(),
    });
  }

  static Future<List<Map<String, dynamic>>> getPendingBeneficiaires() async {
    final db = await database;
    final rows = await db.query('pending_beneficiaires', orderBy: 'id ASC');
    return rows
        .map((r) => {
              'id': r['id'] as int,
              'payload': jsonDecode(r['payload'] as String) as Map<String, dynamic>,
              'created_at': r['created_at'],
            })
        .toList();
  }

  static Future<void> deletePendingBeneficiaire(int id) async {
    final db = await database;
    await db.delete('pending_beneficiaires', where: 'id = ?', whereArgs: [id]);
  }

  static Future<int> countPendingBeneficiaires() async {
    final db = await database;
    final result = await db.rawQuery('SELECT COUNT(*) as count FROM pending_beneficiaires');
    return Sqflite.firstIntValue(result) ?? 0;
  }
}
