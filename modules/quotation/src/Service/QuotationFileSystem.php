<?php


namespace Quotation\Service;


class QuotationFileSystem
{

    public function writeFile(string $file, array $response)
    {
        $file = fopen($file, 'w') or die('Unable to open file!');

        for ($i = 0; $i < count($response); $i++) {
            fwrite($file,
                ($i === 0 ? ('["' . $response[$i]['fullname'] . '",') :
                    ($i === count($response) - 1 ? ('"' . $response[$i]['fullname'] . '"]') :
                        ('"' . $response[$i]['fullname'] . '",'))));
//                ($i === 0 ? ('export const dataCustomers = {data:["' . $response[$i]['fullname'] . '",') :
//                    ($i === count($response) - 1 ? ('"' . $response[$i]['fullname'] . '"]}') :
//                        ('"' . $response[$i]['fullname'] . '",'))));
        }
        fclose($file);
    }

}
