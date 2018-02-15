<?php

namespace AppBundle\Validation;

class Vies
{
    public function isValid($tva)
    {
        $countryCode = substr($tva, 0, 2);
        $vatNo = substr($tva, -strlen($tva) + 2);

        if ($countryCode && $vatNo) {
            $client = new \SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
            $result = $client->checkVat(array(
                'countryCode' => $countryCode,
                'vatNumber' => $vatNo
            ));

            if ($result->valid)
                return true;
            else
                return false;
        } else
            return false;
    }
}
