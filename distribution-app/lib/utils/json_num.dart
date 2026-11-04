/// Lecture tolérante des nombres renvoyés par l'API : le backend sérialise les
/// colonnes décimales en chaînes ("3000.00") et peut renvoyer null ou un autre
/// type. Un cast strict (`as int?`) lèverait une exception de construction,
/// qui laisse un écran blanc en version release.
num jsonNum(dynamic value, [num fallback = 0]) {
  final parsed = value is num
      ? value
      : value is String
          ? num.tryParse(value.trim().replaceAll(',', '.'))
          : null;
  return parsed != null && parsed.isFinite ? parsed : fallback;
}

int jsonInt(dynamic value, [int fallback = 0]) => jsonNum(value, fallback).round();
