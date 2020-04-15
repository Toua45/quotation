<?php

namespace Quotation\Service;

use Dompdf\Dompdf;

class QuotationPdf
{
    public function createPDF($html, $fileName = null)
    {
        $dompdf = new Dompdf();

        // Chargement de la page HTML
        $dompdf->loadHtml($html);

        // Format du document PDF
        $dompdf->setPaper('A4', 'landscape');

        // Rendu du HTML en format PDF
        $dompdf->render();

        // Génère le PDF dans le navigateur (ne pas forcer le téléchargement)
        $dompdf->stream($fileName . '.pdf', [
            "Attachment" => false
        ]);
    }
}
