import 'package:flutter/services.dart' show rootBundle;
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

import '../models/ticket_receipt.dart';
import 'printing/thermal_printer_service.dart';

/// Impression du ticket sur imprimante thermique via le dialogue
/// d'impression système (permet de choisir l'imprimante et le pilote
/// 58 mm / 80 mm). Le QR contient le qr_token existant du ticket :
/// imprimer ou réimprimer ne modifie aucun statut côté serveur.
class TicketPrintService {
  /// Construit le document PDF du ticket (page rouleau [paperMm] × ∞).
  static Future<pw.Document> buildDocument(
    TicketReceipt r, {
    double paperMm = 58,
  }) async {
    final doc = pw.Document();
    final logoBytes = await rootBundle.load('assets/logo_distrubition.jpg');
    final logo = pw.MemoryImage(logoBytes.buffer.asUint8List());

    final font = pw.Font.courier();
    final bold = pw.Font.courierBold();
    final fs = paperMm <= 58 ? 8.0 : 9.5;
    final dashCount = paperMm <= 58 ? 32 : 42;
    final dashes = '-' * dashCount;
    final dots = '.' * dashCount;

    final pageFormat = PdfPageFormat(
      paperMm * PdfPageFormat.mm,
      double.infinity,
      marginAll: 3 * PdfPageFormat.mm,
    );

    pw.Widget sep() => pw.Text(
      dashes,
      style: pw.TextStyle(font: font, fontSize: fs),
      textAlign: pw.TextAlign.center,
    );
    pw.Widget dotSep() => pw.Text(
      dots,
      style: pw.TextStyle(font: font, fontSize: fs),
      textAlign: pw.TextAlign.center,
    );

    pw.Widget row(String label, String? value, {bool strong = false}) {
      if (value == null || value.isEmpty) return pw.SizedBox();
      return pw.Padding(
        padding: const pw.EdgeInsets.symmetric(vertical: 1),
        child: pw.Row(
          crossAxisAlignment: pw.CrossAxisAlignment.start,
          children: [
            pw.Expanded(
              flex: 5,
              child: pw.Text(
                label,
                style: pw.TextStyle(font: font, fontSize: fs),
              ),
            ),
            pw.Expanded(
              flex: 7,
              child: pw.Text(
                value,
                textAlign: pw.TextAlign.right,
                style: pw.TextStyle(font: strong ? bold : font, fontSize: fs),
              ),
            ),
          ],
        ),
      );
    }

    pw.Widget center(String text, {bool isBold = false}) => pw.Center(
      child: pw.Text(
        text,
        textAlign: pw.TextAlign.center,
        style: pw.TextStyle(font: isBold ? bold : font, fontSize: fs),
      ),
    );

    doc.addPage(
      pw.Page(
        pageFormat: pageFormat,
        build: (ctx) => pw.Column(
          crossAxisAlignment: pw.CrossAxisAlignment.stretch,
          children: [
            // ---------- EN-TÊTE ----------
            pw.Center(child: pw.Image(logo, height: paperMm <= 58 ? 42 : 54)),
            pw.SizedBox(height: 4),
            center(TicketReceipt.orgLine1, isBold: true),
            center(TicketReceipt.orgLine2, isBold: true),
            pw.SizedBox(height: 2),
            center(TicketReceipt.title, isBold: true),
            sep(),
            center(TicketReceipt.copyLabel),
            sep(),

            // ---------- BÉNÉFICIAIRE ----------
            row('N° CARTE / REF', r.cardRef, strong: true),
            row('BENEFICIAIRE', r.beneficiaryName, strong: true),
            row('NB BENEFICIAIRES', r.householdSize),
            row('SITE', r.site),
            row('CAMPAGNE', r.campaign),
            row('SERVI PAR', r.servedBy),
            row('N° RECU', r.ticketCode, strong: true),
            row('ID COUPON', r.couponId),
            row('TYPE', r.distributionType),
            row('DATE TRANSACTION', r.transactionDate),
            sep(),

            // ---------- QR AU MILIEU ----------
            pw.SizedBox(height: 4),
            pw.Center(
              child: pw.BarcodeWidget(
                barcode: pw.Barcode.qrCode(),
                data: r.qrToken,
                width: paperMm <= 58 ? 105 : 130,
                height: paperMm <= 58 ? 105 : 130,
                color: PdfColors.black,
              ),
            ),
            pw.SizedBox(height: 2),
            center(TicketReceipt.scanHint, isBold: true),
            pw.SizedBox(height: 4),
            sep(),

            // ---------- ARTICLES ----------
            center('ARTICLES DISTRIBUÉS', isBold: true),
            pw.SizedBox(height: 2),
            pw.Row(
              children: [
                pw.Expanded(
                  child: pw.Text(
                    'ARTICLE',
                    style: pw.TextStyle(font: bold, fontSize: fs),
                  ),
                ),
                pw.Text(
                  'QUANTITÉ',
                  style: pw.TextStyle(font: bold, fontSize: fs),
                ),
              ],
            ),
            dotSep(),
            if (r.items.isEmpty)
              pw.Text(
                'Aucun article renseigné',
                style: pw.TextStyle(font: font, fontSize: fs),
              )
            else
              ...r.items.map(
                (it) => pw.Padding(
                  padding: const pw.EdgeInsets.symmetric(vertical: 1),
                  child: pw.Row(
                    crossAxisAlignment: pw.CrossAxisAlignment.start,
                    children: [
                      pw.Expanded(
                        child: pw.Text(
                          it.name,
                          style: pw.TextStyle(font: font, fontSize: fs),
                        ),
                      ),
                      pw.Text(
                        it.quantity,
                        style: pw.TextStyle(font: font, fontSize: fs),
                      ),
                    ],
                  ),
                ),
              ),
            sep(),

            // ---------- PIED ----------
            pw.Row(
              mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
              children: [
                pw.Text(
                  'DATE: ${r.datePart}',
                  style: pw.TextStyle(font: font, fontSize: fs),
                ),
                pw.Text(
                  'HEURE: ${r.timePart}',
                  style: pw.TextStyle(font: font, fontSize: fs),
                ),
              ],
            ),
            row('TERMINAL', r.terminalId),
            row('TICKET', r.ticketCode, strong: true),
            pw.SizedBox(height: 6),
            center(TicketReceipt.keepHint),
            pw.SizedBox(height: 2),
            center(TicketReceipt.footer, isBold: true),
          ],
        ),
      ),
    );

    return doc;
  }

  /// Impression via le dialogue natif complet : l'utilisateur y choisit
  /// lui-même l'imprimante et le format (pilotes OS USB/BT/Wi-Fi).
  static Future<void> printTicket(
    TicketReceipt r, {
    double paperMm = 58,
  }) async {
    final doc = await buildDocument(r, paperMm: paperMm);
    await Printing.layoutPdf(
      onLayout: (_) async => doc.save(),
      name: 'ticket_${r.ticketCode}',
    );
  }

  /// Impression directe vers une imprimante déjà sélectionnée, sans
  /// boîte de dialogue. Retourne le résultat réel de l'envoi.
  static Future<PrintResult> printViaPrinter(
    Printer printer,
    TicketReceipt r, {
    double paperMm = 58,
  }) async {
    try {
      final doc = await buildDocument(r, paperMm: paperMm);
      final ok = await Printing.directPrintPdf(
        printer: printer,
        name: 'ticket_${r.ticketCode}',
        onLayout: (_) async => doc.save(),
      );
      return ok
          ? const PrintResult.ok()
          : const PrintResult.fail("L'imprimante a refusé le document.");
    } catch (e) {
      return PrintResult.fail('Erreur d\'impression : $e');
    }
  }
}
