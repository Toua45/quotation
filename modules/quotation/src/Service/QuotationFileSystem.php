<?php

namespace Quotation\Service;

class QuotationFileSystem
{
    /**
     * $file : création fichier : admin/data-customer.js
     */
    public function writeFile(string $file, array $response)
    {
        $file = fopen($file, 'w') or die('Unable to open file!');

        for ($i = 0; $i < count($response); $i++) {
            fwrite($file, // Rédige directement dans le fichier data-customer.js
                ($i === 0 ? ('["' . $response[$i]['fullname'] . '",') :
                    ($i === count($response) - 1 ? ('"' . $response[$i]['fullname'] . '"]') :
                        ('"' . $response[$i]['fullname'] . '",'))));
        }
        fclose($file);
    }
}
