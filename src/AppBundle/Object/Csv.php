<?php

namespace AppBundle\Object;

class Csv
{
    // Les en-têtes que doit contenir le fichier CSV
    private static $trueHeader = array(
        "tvaintra",
        "raison sociale",
        "adresse",
        "complement d'adresse",
        "code postal",
        "ville",
        "pays",
        "nom d'utilisateur",
        "prenom d'utilisateur",
        "adresse email",
        "numero de telephone",
        "profil utilisateur",
        "siret",
        "DUN",
        "INSEE",
        "gln",
        "accord",
        "langue",
        "type de client",
        "piece jointe",
        "groupe",
        "code client"
    );

    private $size;
    private $header;
    private $content;

    public function lire_csv($path)
    {
        $row = 0;

        if ($handle = fopen($path, "r")) {
            // On convertit le fichier en UTF-8
            $fileContent = file_get_contents($path);
            if (mb_check_encoding($fileContent, 'UTF-8'))
                file_put_contents($path, $fileContent);
            else
                file_put_contents($path, utf8_encode($fileContent));

            $this->header = fgetcsv($handle, 0, ";");

            while ($data = fgetcsv($handle, 0, ";")) {
                $this->content[$row]['tva_intra'] = $data[0];
                $this->content[$row]['raison_sociale'] = $data[1];
                $this->content[$row]['adresse'] = $data[2];
                $this->content[$row]['complement'] = $data[3];
                $this->content[$row]['code_postal'] = $data[4];
                $this->content[$row]['ville'] = $data[5];
                $this->content[$row]['pays'] = $data[6];
                $this->content[$row]['nom_utilisateur'] = $data[7];
                $this->content[$row]['prenom_utilisateur'] = $data[8];
                $this->content[$row]['email'] = $data[9];
                $this->content[$row]['telephone'] = $data[10];
                $this->content[$row]['profil'] = $data[11];
                $this->content[$row]['siret'] = $data[12];
                $this->content[$row]['DUN'] = $data[13];
                $this->content[$row]['INSEE'] = $data[14];
                $this->content[$row]['gln'] = $data[15];
                $this->content[$row]['accord'] = $data[16];
                $this->content[$row]['langue'] = $data[17];
                $this->content[$row]['type_client'] = $data[18];
                $this->content[$row]['piece_jointe'] = $data[19];
                $this->content[$row]['groupe'] = $data[20];
                $this->content[$row]['code_client'] = $data[21];
                $row++;
            }
            $this->size = $row;
            fclose($handle);
        }
    }

    public function verif_header()
    {
        if (count(Csv::$trueHeader) == count($this->header)) {
            /*for ($i=0; $i< count($this->trueHeader); $i++) {
                similar_text($this->trueHeader[$i], $this->header[$i], $percent);
                if ($percent < 60){
                    return false;
                }
            }*/
            return true;
        } else {
            return false;
        }
    }

    public function arrayToCsv(array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false)
    {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');

        $output = array();
        foreach ($fields as $field) {
            if ($field === null && $nullToMysqlNull) {
                $output[] = 'NULL';
                continue;
            }

            // Enclose fields containing $delimiter, $enclosure or whitespace
            if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
                $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
            } else {
                $output[] = $field;
            }
        }

        return implode($delimiter, $output);
    }
}
