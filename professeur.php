<?php
class Professeur {
    private $id;
    private $nom;
    private $prenom;
    private $cin;
    private $email;
    private $departement;
    private $matieres = [];

    public function __construct($nom, $prenom, $cin, $email, $departement) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->cin = $cin;
        $this->email = $email;
        $this->departement = $departement;
    }

    public function ajouter() {
        $db = Database::getInstance();
        
        // Vérifier si le CIN existe déjà
        $stmt = $db->prepare("SELECT id FROM professeurs WHERE cin = ?");
        $stmt->execute([$this->cin]);
        
        if ($stmt->fetch()) {
            throw new Exception("Un professeur avec ce CIN existe déjà.");
        }

        // Insérer le professeur
        $stmt = $db->prepare("INSERT INTO professeurs (nom, prenom, cin, email, departement) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$this->nom, $this->prenom, $this->cin, $this->email, $this->departement]);
        
        $this->id = $db->lastInsertId();
        
        // Ajouter les matières
        foreach ($this->matieres as $matiere) {
            $matiere->lierProfesseur($this->id);
        }
        
        return $this->id;
    }

    public function ajouterMatiere(Matiere $matiere) {
        $this->matieres[] = $matiere;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    // ... autres getters
}
?>