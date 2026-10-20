import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../models/ticket_receipt.dart';

/// Rendu écran du ticket façon reçu thermique (noir/blanc, monospace,
/// séparateurs en tirets). Le même agencement est reproduit dans le PDF
/// d'impression (TicketPrintService).
class ThermalTicketView extends StatelessWidget {
  final TicketReceipt receipt;
  final double width;

  const ThermalTicketView({super.key, required this.receipt, this.width = 300});

  static const _mono = TextStyle(
    fontFamily: 'monospace',
    fontSize: 11,
    color: Colors.black,
    height: 1.35,
  );

  static const _monoBold = TextStyle(
    fontFamily: 'monospace',
    fontSize: 11,
    color: Colors.black,
    fontWeight: FontWeight.bold,
    height: 1.35,
  );

  @override
  Widget build(BuildContext context) {
    return Container(
      width: width,
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 16),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // ---------- EN-TÊTE ----------
          Image.asset('assets/logo_distrubition.jpg', height: 52),
          const SizedBox(height: 8),
          const Text(
            TicketReceipt.orgLine1,
            style: _monoBold,
            textAlign: TextAlign.center,
          ),
          const Text(
            TicketReceipt.orgLine2,
            style: _monoBold,
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4),
          const Text(
            TicketReceipt.title,
            style: _monoBold,
            textAlign: TextAlign.center,
          ),
          _dashes(),
          const Text(
            TicketReceipt.copyLabel,
            style: _mono,
            textAlign: TextAlign.center,
          ),
          _dashes(),

          // ---------- BÉNÉFICIAIRE ----------
          _row('N° CARTE / RÉF', receipt.cardRef),
          _row('BÉNÉFICIAIRE', receipt.beneficiaryName),
          _row('NB BÉNÉFICIAIRES', receipt.householdSize),
          _row('SITE', receipt.site),
          _row('CAMPAGNE', receipt.campaign),
          _row('SERVI PAR', receipt.servedBy),
          _row('N° REÇU', receipt.ticketCode),
          _row('ID COUPON', receipt.couponId),
          _row('TYPE', receipt.distributionType),
          _row('DATE TRANSACTION', receipt.transactionDate),
          _dashes(),

          // ---------- QR AU MILIEU ----------
          const SizedBox(height: 6),
          QrImageView(
            data: receipt.qrToken,
            version: QrVersions.auto,
            size: 160,
            backgroundColor: Colors.white,
            eyeStyle: const QrEyeStyle(
              eyeShape: QrEyeShape.square,
              color: Colors.black,
            ),
            dataModuleStyle: const QrDataModuleStyle(
              dataModuleShape: QrDataModuleShape.square,
              color: Colors.black,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            TicketReceipt.scanHint,
            style: _monoBold,
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          _dashes(),

          // ---------- ARTICLES ----------
          const Text('ARTICLES DISTRIBUÉS', style: _monoBold),
          const SizedBox(height: 4),
          const Row(
            children: [
              Expanded(child: Text('ARTICLE', style: _monoBold)),
              Text('QUANTITÉ', style: _monoBold),
            ],
          ),
          _dashes(char: '.'),
          if (receipt.items.isEmpty)
            const Text('Aucun article renseigné', style: _mono)
          else
            ...receipt.items.map(
              (it) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 1),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(child: Text(it.name, style: _mono)),
                    Text(it.quantity, style: _mono),
                  ],
                ),
              ),
            ),
          _dashes(),

          // ---------- PIED ----------
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('DATE: ${receipt.datePart}', style: _mono),
              Text('HEURE: ${receipt.timePart}', style: _mono),
            ],
          ),
          if (receipt.terminalId != null) _row('TERMINAL', receipt.terminalId),
          _row('TICKET', receipt.ticketCode),
          const SizedBox(height: 8),
          const Text(
            TicketReceipt.keepHint,
            style: _mono,
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4),
          const Text(
            TicketReceipt.footer,
            style: _monoBold,
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _row(String label, String? value) {
    if (value == null || value.isEmpty) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 1),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(flex: 5, child: Text(label, style: _mono)),
          Expanded(
            flex: 7,
            child: Text(value, style: _monoBold, textAlign: TextAlign.right),
          ),
        ],
      ),
    );
  }

  Widget _dashes({String char = '-'}) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final w = constraints.maxWidth.isFinite ? constraints.maxWidth : width;
        final count = (w / 5.6).floor(); // ~largeur d'un tiret monospace 11pt
        return Text(
          char * count,
          maxLines: 1,
          overflow: TextOverflow.clip,
          style: _mono,
        );
      },
    );
  }
}
