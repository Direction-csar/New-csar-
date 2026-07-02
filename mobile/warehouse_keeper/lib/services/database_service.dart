import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../models/stock_movement.dart';

class DatabaseService {
  static Database? _db;

  static Future<Database> get database async {
    _db ??= await _initDb();
    return _db!;
  }

  static Future<Database> _initDb() async {
    final path = join(await getDatabasesPath(), 'csar_warehouse.db');
    return await openDatabase(
      path,
      version: 1,
      onCreate: (db, version) async {
        await db.execute('''
          CREATE TABLE stock_movements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            warehouse_id INTEGER NOT NULL,
            movement_date TEXT NOT NULL,
            label TEXT NOT NULL,
            position TEXT,
            entry_sacs REAL DEFAULT 0,
            entry_kg REAL DEFAULT 0,
            exit_sacs REAL DEFAULT 0,
            exit_kg REAL DEFAULT 0,
            balance_sacs REAL DEFAULT 0,
            balance_kg REAL DEFAULT 0,
            observation TEXT,
            reference TEXT UNIQUE NOT NULL,
            movement_type TEXT NOT NULL,
            status TEXT DEFAULT 'draft',
            is_synced INTEGER DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
          )
        ''');

        await db.execute('''
          CREATE TABLE warehouses (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            code TEXT,
            location TEXT,
            capacity REAL DEFAULT 0,
            current_stock REAL DEFAULT 0,
            status TEXT DEFAULT 'active'
          )
        ''');
      },
    );
  }

  // INSERT local
  static Future<int> insertMovement(StockMovement m) async {
    final db = await database;
    return await db.insert('stock_movements', m.toLocalDb());
  }

  // GET pending sync
  static Future<List<StockMovement>> getPendingSync() async {
    final db = await database;
    final maps = await db.query('stock_movements', where: 'is_synced = 0');
    return maps.map((m) => StockMovement.fromLocalDb(m)).toList();
  }

  // GET by warehouse
  static Future<List<StockMovement>> getMovementsByWarehouse(int warehouseId) async {
    final db = await database;
    final maps = await db.query(
      'stock_movements',
      where: 'warehouse_id = ?',
      whereArgs: [warehouseId],
      orderBy: 'movement_date DESC',
    );
    return maps.map((m) => StockMovement.fromLocalDb(m)).toList();
  }

  // MARK synced
  static Future<void> markSynced(int localId) async {
    final db = await database;
    await db.update(
      'stock_movements',
      {'is_synced': 1, 'status': 'synced'},
      where: 'id = ?',
      whereArgs: [localId],
    );
  }

  // DELETE all
  static Future<void> clearMovements() async {
    final db = await database;
    await db.delete('stock_movements');
  }

  // WAREHOUSES
  static Future<void> saveWarehouses(List<Map<String, dynamic>> list) async {
    final db = await database;
    await db.delete('warehouses');
    for (final w in list) {
      await db.insert('warehouses', w);
    }
  }

  static Future<List<Map<String, dynamic>>> getWarehouses() async {
    final db = await database;
    return await db.query('warehouses', orderBy: 'name');
  }
}
