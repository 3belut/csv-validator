<?php

namespace AppBundle\Validation;

/**
 * Cette classe permet la validation d'adresses email.
 */
class MailValidation
{
    /**
     * Cette fonction vérifie la validité d'une adresse email.
     *
     * Elle vérifie le format syntaxiquement, puis recherche d'éventuels enregistrements MX associés au domaine.
     * L'email est considéré invalide si la syntaxe n'est pas correcte ou si aucun enregistrement MX n'est trouvé.
     *
     * @param string $email
     *      L'adresse email à vérifier
     * @return bool
     *      true si l'email est valide, false sinon
     */
    public function isValid($email)
    {
        // Si le format est bon
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $parts = explode('@', $email);
            $domain = end($parts);

            // Et si le domaine existe
            if (checkdnsrr($domain))
                return true; // On considère l'email valide
        }

        return false;
    }
}
