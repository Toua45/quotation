<?php

namespace Quotation\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class QuotationPdf
{
    public function createPDF($html, $fileName = null)
    {
        $pdfOptions = new Options();

        // Définition de la police du document PDF
        $pdfOptions->set('defaultFont', 'sans-serif');

        // Permet d'accéder aux sites distants pour héberger des images
        $pdfOptions->set('isRemoteEnabled', true);

        // Permet d'activer l'intégration de php dans la balise <script type="text/php"> ... </script>
        $pdfOptions->set('isPhpEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        // Chargement de la page HTML
        $dompdf->loadHtml($html);

        // Format du document PDF
        $dompdf->setPaper('A4', 'portrait');

        // Rendu du HTML en format PDF
        $dompdf->render();

        // Génère le PDF dans le navigateur (ne pas forcer le téléchargement)
        $dompdf->stream($fileName . '.pdf', [
            "Attachment" => false
        ]);
    }
}
