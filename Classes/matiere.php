<?php

class Matiere {
    private $id;
    private $nom;

    public function __construct($nom) {
        $this->nom = $nom;
    }

    public function sauvegarder() {
        $db = Database::getInstance();
        
        // Vérifier si la matière existe déjà
        $stmt = $db->prepare("SELECT id FROM matieres WHERE nom = ?");
        $stmt->execute([$this->nom]);
        $row = $stmt->fetch();
        
        if ($row) {
            $this->id = $row['id'];
        } else {
            $stmt = $db->prepare("INSERT INTO matieres (nom) VALUES (?)");
            $stmt->execute([$this->nom]);
            $this->id = $db->lastInsertId();
        }
        
        return $this->id;
    }

    public function lierProfesseur($id_professeur) {
        $db = Database::getInstance();
        
        // Vérifier si la liaison existe déjà
        $stmt = $db->prepare("SELECT * FROM professeur_matieres WHERE id_professeur = ? AND id_matiere = ?");
        $stmt->execute([$id_professeur, $this->id]);
        
        if (!$stmt->fetch()) {
            $stmt = $db->prepare("INSERT INTO professeur_matieres (id_professeur, id_matiere) VALUES (?, ?)");
            $stmt->execute([$id_professeur, $this->id]);
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
}

?>