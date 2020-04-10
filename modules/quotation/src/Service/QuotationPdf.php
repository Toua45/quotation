<?php

namespace Quotation\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class QuotationPdf
{
    public function createPDF($html, Dompdf $dompdf, $fileName = null)
    {
//        // Récupère le HTML généré dans le fichier twig
//        if ($template !== null) {
//            $html = $this->twig->render($template, $data);
//        }

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
