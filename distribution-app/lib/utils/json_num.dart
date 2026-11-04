/// Lecture tolérante des nombres renvoyés par l'API : le backend sérialise les
/// colonnes décimales en chaînes ("3000.00") et peut renvoyer null ou un autre
/// type. Un cast strict (`as int?`) lèverait une exception de construction,
/// qui laisse un écran blanc en version release.
num jsonNum(dynamic value, [num fallback = 0]) {
  if (value is num) return value;
  if (value is String) return num.tryParse(value.trim().replaceAll(',', '.')) ?? fallback;
  return fallback;
}

int jsonInt(dynamic value, [int fallback = 0]) => jsonNum(value, fallback).round();
