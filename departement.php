<?php
class Departement {
    const DEPARTEMENTS = [
        'Mathématiques',
        'Physiques',
        'Informatique',
        'Cyber sécurité',
        'Eléctronique',
        'Industriel',
        'Réseau'
    ];

    public static function getAll() {
        return self::DEPARTEMENTS;
    }

    public static function isValid($departement) {
        return in_array($departement, self::DEPARTEMENTS);
    }
}
?>