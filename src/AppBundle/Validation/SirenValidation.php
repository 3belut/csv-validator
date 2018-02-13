<?php

namespace AppBundle\Validation;

/**
 * Cette classe permet la validation des données des entreprises (SIREN, SIRET, raison sociale, adresse, etc.).
 *
 * Elle fait appel à la base Sirene OpenDataSoft : https://data.opendatasoft.com/explore/dataset/sirene@public/
 */
class SirenValidation
{
    /**
     * Cette fonction récupère des données sur les entreprises.
     *
     * Elle retourne un tableau des établissements trouvés avec certaines données.
     *
     * @param array $sirets
     *      Un tableau contenant les numéro SIRET des établissements à chercher
     * @return array
     *      Tableau contenant les établissements trouvés et leurs données, identifiés par leur numéro SIRET en clé
     */
    public function fetchDataBySiret($sirets)
    {
        // Le tableau qui contiendra les résultats
        $entreprises = array();

        // On regroupe les requêtes par paquets de 50 afin de gagner du temps
        $i = 0;
        while ($i < count($sirets)) {
            $url = 'https://data.opendatasoft.com/api/records/1.0/search/?rows=-1&dataset=sirene%40public&q=';
            foreach (array_slice($sirets, $i, 300) as $siret) {
                if (strlen($siret) == 14)
                    $url = $url . 'siret%3D' . $siret . '+OR+';
            }
            $i += 300;

            // On retire le dernier OR
            $url = substr($url, 0, -4);

            // On exécute la requête
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);

            // On décode les résultats dans un tableau
            $result = json_decode($json, true);

            // On remplit notre liste des entreprises avec les données qui nous intéressent
            if (array_key_exists('records', $result)) {
                foreach ($result['records'] as $record) {
                    $siret = $record['fields']['siret'];
                    $entreprise = array(
                        'raison_sociale' => array_key_exists('nomen_long', $record['fields']) ? $record['fields']['nomen_long'] : '',
                        'code_postal' => array_key_exists('codpos', $record['fields']) ? $record['fields']['codpos'] : '',
                        'ville' => array_key_exists('libcom', $record['fields']) ? $record['fields']['libcom'] : '',
                        'adresse' => array_key_exists('l4_normalisee', $record['fields']) ? $record['fields']['l4_normalisee'] : ''
                    );

                    $entreprises[$siret] = $entreprise;
                }
            }
        }

        return $entreprises;
    }

    /**
     * Cette fonction récupère des données sur les entreprises.
     *
     * Elle retourne un tableau des entreprises trouvées avec certaines données.
     *
     * @param array $sirens
     *      Un tableau contenant les numéro SIREN des entreprises à chercher
     * @return array
     *      Tableau contenant les entreprises trouvées et leurs données, identifiées par leur numéro SIREN en clé
     */
    public function fetchDataBySiren($sirens)
    {
        // Le tableau qui contiendra les résultats
        $entreprises = array();

        // On regroupe les requêtes par paquets de 100 afin de gagner du temps
        $i = 0;
        while ($i < count($sirens)) {
            $url = 'https://data.opendatasoft.com/api/records/1.0/search/?rows=-1&dataset=sirene%40public&q=';
            foreach (array_slice($sirens, $i, 370) as $siren) {
                if (strlen($siren) == 9)
                    $url = $url . 'siren%3D' . $siren . '+OR+';
            }
            $i += 370;

            // On retire le dernier OR
            $url = substr($url, 0, -4);

            // On exécute la requête
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);

            // On décode les résultats dans un tableau
            $result = json_decode($json, true);

            // On remplit notre liste des entreprises avec les données qui nous intéressent
            if (array_key_exists('records', $result)) {
                foreach ($result['records'] as $record) {
                    $siren = $record['fields']['siren'];
                    $entreprise = array(
                        'raison_sociale' => array_key_exists('nomen_long', $record['fields']) ? $record['fields']['nomen_long'] : '',
                        'code_postal' => array_key_exists('codpos', $record['fields']) ? $record['fields']['codpos'] : '',
                        'ville' => array_key_exists('libcom', $record['fields']) ? $record['fields']['libcom'] : '',
                        'adresse' => array_key_exists('l4_normalisee', $record['fields']) ? $record['fields']['l4_normalisee'] : ''
                    );

                    $entreprises[$siren] = $entreprise;
                }
            }
        }

        return $entreprises;
    }
}
