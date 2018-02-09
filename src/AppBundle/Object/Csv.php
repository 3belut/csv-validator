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
        "code client"
    );

    private $size;
    private $header;
    private $content;
    private $path;

    /**
     * @return array
     */
    public static function getTrueHeader()
    {
        return self::$trueHeader;
    }

    /**
     * @param array $trueHeader
     */
    public static function setTrueHeader($trueHeader)
    {
        self::$trueHeader = $trueHeader;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param mixed $header
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
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
                $this->content[$row]['code_client'] = $data[19];
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

    /**
     * Cette fonction retourne le fichier CSV contenant uniquement les lignes passées en paramètres.
     *
     * @param array $indiceLignes
     *      Les numéros de ligne à garder.
     * @param string $delimiteur
     * @param string $enclosure
     * @return null|string
     *      Le nouveau fichier CSV.
     */
    public function getFinalCsv(array $indiceLignes, $delimiteur = ';', $enclosure = '"')
    {
        $output = null;
        $l = 0;
        while ($l < (count(self::getTrueHeader()) - 1)) {
            $output = $output . $enclosure . self::getTrueHeader()[$l] . $enclosure . $delimiteur;
            $l++;
        }
        $output = $output . $enclosure . self::getTrueHeader()[$l] . $enclosure . "\r\n";
        for ($i = 0; $i < count($indiceLignes); $i++) {
            $ligne = ($this->getContent())[array_values($indiceLignes)[$i]];
            $j = 0;
            while ($j < (count($ligne) - 1)) {
                $output = $output . $enclosure . array_values($ligne)[$j] . $enclosure . $delimiteur;
                $j++;
            }
            $output = $output . $enclosure . array_values($ligne)[$j] . $enclosure . "\r\n";
        }
        return $output;
    }
}
