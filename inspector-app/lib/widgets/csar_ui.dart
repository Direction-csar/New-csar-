import 'package:flutter/material.dart';
import '../theme/csar_theme.dart';

/// Carte KPI institutionnelle : icône, valeur, libellé, sous-titre.
class KpiCard extends StatelessWidget {
  final IconData icon;
  final String value;
  final String label;
  final String? subtitle;
  final Color color;
  final Color? iconBg;
  final VoidCallback? onTap;

  const KpiCard({
    super.key,
    required this.icon,
    required this.value,
    required this.label,
    this.subtitle,
    this.color = CsarColors.blue,
    this.iconBg,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: CsarColors.border),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(9),
                decoration: BoxDecoration(
                  color: (iconBg ?? color).withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: iconBg ?? color, size: 20),
              ),
              const SizedBox(height: 10),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                  color: CsarColors.text,
                  height: 1.1,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 3),
              Text(
                label,
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: CsarColors.text),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              if (subtitle != null)
                Text(
                  subtitle!,
                  style: const TextStyle(fontSize: 11, color: CsarColors.textMuted),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Badge de statut normalisé (couleurs institutionnelles).
class StatusBadge extends StatelessWidget {
  final String label;
  final Color color;
  final Color bg;
  final IconData? icon;

  const StatusBadge._(this.label, this.color, this.bg, this.icon);

  factory StatusBadge.of(String status) {
    switch (status) {
      case 'executee':
      case 'verifiee':
      case 'verifie':
      case 'completed':
        return const StatusBadge._('Exécutée', CsarColors.green, CsarColors.greenLight, Icons.check_circle);
      case 'transmise_magasinier':
      case 'transmise':
        return const StatusBadge._('Transmise', CsarColors.blue, CsarColors.blueLight, Icons.send);
      case 'affectee_inspecteur':
      case 'affectee':
        return const StatusBadge._('Affectée', CsarColors.amber, CsarColors.amberLight, Icons.assignment_ind);
      case 'soumise':
        return const StatusBadge._('Soumise', CsarColors.textMuted, Color(0xFFF1F5F9), Icons.hourglass_top);
      case 'anomalie':
      case 'rejetee':
      case 'rejected':
        return const StatusBadge._('Anomalie', CsarColors.red, CsarColors.redLight, Icons.warning_amber);
      case 'en_attente':
      case 'pending':
        return const StatusBadge._('En attente', CsarColors.orange, CsarColors.orangeLight, Icons.schedule);
      case 'annulee':
        return const StatusBadge._('Annulée', CsarColors.textMuted, Color(0xFFF1F5F9), Icons.cancel);
      default:
        return StatusBadge._(status, CsarColors.textMuted, const Color(0xFFF1F5F9), null);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 11, color: color),
            const SizedBox(width: 4),
          ],
          Text(
            label,
            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: color),
          ),
        ],
      ),
    );
  }
}

/// En-tête de section de carte.
class SectionHeader extends StatelessWidget {
  final String title;
  final IconData? icon;
  final Widget? trailing;

  const SectionHeader({super.key, required this.title, this.icon, this.trailing});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 18, 16, 8),
      child: Row(
        children: [
          if (icon != null) ...[
            Icon(icon, size: 17, color: CsarColors.blue),
            const SizedBox(width: 8),
          ],
          Expanded(
            child: Text(
              title,
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w700,
                color: CsarColors.text,
              ),
            ),
          ),
          ?trailing,
        ],
      ),
    );
  }
}

/// État vide illustré.
class EmptyState extends StatelessWidget {
  final IconData icon;
  final String title;
  final String? subtitle;

  const EmptyState({super.key, required this.icon, required this.title, this.subtitle});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(18),
              decoration: const BoxDecoration(
                color: CsarColors.blueLight,
                shape: BoxShape.circle,
              ),
              child: Icon(icon, size: 34, color: CsarColors.blue),
            ),
            const SizedBox(height: 14),
            Text(title,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: CsarColors.text)),
            if (subtitle != null) ...[
              const SizedBox(height: 6),
              Text(subtitle!,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 13, color: CsarColors.textMuted)),
            ],
          ],
        ),
      ),
    );
  }
}

/// Bannière offline / erreur réseau.
class OfflineBanner extends StatelessWidget {
  final String? message;
  final bool offline;
  final VoidCallback? onRetry;

  const OfflineBanner({super.key, this.message, this.offline = false, this.onRetry});

  @override
  Widget build(BuildContext context) {
    final isOffline = offline || message != null;
    if (!isOffline) return const SizedBox.shrink();
    final isNet = message == null;
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.fromLTRB(16, 8, 16, 0),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: isNet ? CsarColors.amberLight : CsarColors.redLight,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: isNet ? CsarColors.amber : CsarColors.red, width: 0.8),
      ),
      child: Row(
        children: [
          Icon(isNet ? Icons.wifi_off_rounded : Icons.error_outline,
              size: 18, color: isNet ? CsarColors.amber : CsarColors.red),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              isNet
                  ? 'Mode hors ligne — les données affichées peuvent être anciennes.'
                  : message!,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: FontWeight.w600,
                color: isNet ? const Color(0xFF92400E) : const Color(0xFF991B1B),
              ),
            ),
          ),
          if (onRetry != null)
            TextButton(onPressed: onRetry, child: const Text('Réessayer', style: TextStyle(fontSize: 12))),
        ],
      ),
    );
  }
}

/// En-tête institutionnel réutilisable (logo texte CSAR + sous-titre).
class CsarHeader extends StatelessWidget {
  final String title;
  final String? subtitle;
  final Widget? trailing;

  const CsarHeader({super.key, required this.title, this.subtitle, this.trailing});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(18, 18, 18, 16),
      decoration: const BoxDecoration(
        color: CsarColors.navy,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(22)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                ),
                child: const Text(
                  'CSAR',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    fontSize: 13,
                    letterSpacing: 1.2,
                  ),
                ),
              ),
              const Spacer(),
              ?trailing,
            ],
          ),
          const SizedBox(height: 14),
          Text(
            title,
            style: const TextStyle(color: Colors.white, fontSize: 21, fontWeight: FontWeight.w800),
          ),
          if (subtitle != null) ...[
            const SizedBox(height: 4),
            Text(subtitle!, style: TextStyle(color: Colors.white.withValues(alpha: 0.75), fontSize: 13)),
          ],
        ],
      ),
    );
  }
}
