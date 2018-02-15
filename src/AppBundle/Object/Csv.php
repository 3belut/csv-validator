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

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Cette fonction remplit l'objet à partir d'un fichier CSV.
     *
     * Les encodages supportés sont l'UTF-8 et l'ISO 8859-1 ou apparenté (alphabet occidental).
     *
     * @param string $path
     *      Le chemin vers le fichier CSV.
     */
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

    public static function verif_header($path)
    {
        if ($handle = fopen($path, "r")) {
            // On convertit le fichier en UTF-8
            $fileContent = file_get_contents($path);
            if (mb_check_encoding($fileContent, 'UTF-8'))
                file_put_contents($path, $fileContent);
            else
                file_put_contents($path, utf8_encode($fileContent));

            $header = fgetcsv($handle, 0, ";");
            if (count(Csv::$trueHeader) == count($header)) {
                /*for ($i=0; $i< count($this->trueHeader); $i++) {
                    similar_text($this->trueHeader[$i], $this->header[$i], $percent);
                    if ($percent < 60){
                        return false;
                    }
                }*/
                fclose($handle);
                return true;
            } else {
                fclose($handle);
                return false;
            }
        }else{
            return false;
        }
    }

    public static function array2Csv($content, $valid)
    {
        $file = '';
        $header = self::$trueHeader;
        if (!$valid)
            $header[] = 'erreurs';
        array_unshift($content, $header);
        foreach ($content as $row) {
            foreach ($row as $field) {
                $file .= $field . ';';
            }
            // On remplace le point-virgule par un retour à la ligne
            $file = substr($file, 0, -1);
            $file .= "\r\n";
        }
        // On retire le dernier retour à la ligne
        $file = substr($file, 0, -2);

        return $file;
    }
}
