<?php
    $db = new PDO("mysql:host=localhost; dbname=database", "root", "");   
?>
<!DOCTYPE>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Je vais insérer les notes</title>
    </head>
    <body>
        <h1>Les élèves n'ayant absolument aucune note</h1>
        <?php
            $classeCF = 'SELECT c.classe_id AS idClasse, c.libelle_classe_fr AS classe, n.libelle_fr AS niveau
                        FROM classe c
                        JOIN niveau n ON n.id = c.niveau_id
                        WHERE c.annee_scolaire_id = 1 AND n.groupe_formation_id = 2';
            $requete = $db->query($classeCF);
            $classes = $requete->fetchAll();
        ?>
        <form action="" name="" method="post">
            <label for="classe">Entrez l'identifiant d'une classe:</label>
            <select name="classe" id="classe">
                <option value="">--- Sélectionnez une classe ---</option>
                <?php
                    foreach ($classes as $classe) {
                        
                        echo "<option value=".$classe['idClasse'].">".$classe['niveau']." - ".$classe['classe']." => ".$classe['idClasse']."</option>";
                        
                    }
                ?>
            </select>
            <br>
            <label for="examen">Entrez l'identifiant de l'examen:</label>
            <input type="integer" id="examen" name="examen" value="1">
            <br><br>
            <input type="submit" value=" Afficher les élèves " class="ui primary button" name="sms">
        </form>
        <?php
            if(isset($_POST['sms']))
            {
                if(!empty($_POST['classe']) && !empty($_POST['examen']))
                {
                    if(!is_numeric($_POST['classe']) || !is_numeric($_POST['examen']))
                    {
                        echo"<script>alert('Valeurs d\'identifiant incorrectes!!!')</script>";
                        die();
                    }
                    $classe = $_POST['classe'];
                    $examen = $_POST['examen'];
                    try {
                        $codeSql = 'SELECT DISTINCT(e1.eleve_id)
                                    FROM eleve e1
                                    JOIN frequenter f ON e1.eleve_id = f.eleve_id
                                    JOIN classe c ON f.classe_id = c.classe_id
                                    WHERE c.classe_id = '.$classe.' AND e1.eleve_id NOT IN 
                                        (
                                            SELECT DISTINCT(e.eleve_id)
                                            FROM eleve e
                                            JOIN note n ON e.eleve_id = n.eleve_id
                                            JOIN frequenter fq ON fq.eleve_id = e.eleve_id
                                            JOIN classe cl ON fq.classe_id = fq.classe_id
                                            WHERE cl.classe_id = '.$classe.' AND n.eleve_id = fq.eleve_id AND fq.classe_id = cl.classe_id AND n.examen_id = '.$examen.'
                                        )';
                        
                        $requete = $db->query($codeSql);
                        $eleves = $requete->fetchAll();

                        $requete = $db->query('SELECT c.classe_id AS idClasse, c.libelle_classe_fr AS classe, n.libelle_fr AS niveau
                                                FROM classe c
                                                JOIN niveau n ON n.id = c.niveau_id
                                                WHERE c.classe_id = '.$classe.' AND c.annee_scolaire_id = 1 AND n.groupe_formation_id = 2'
                                            );
                        $laclasse = $requete->fetch();
                        
                        echo "Il y a <strong>".count($eleves)."</strong> qui n'ont pas de notes en <strong>".$laclasse['niveau']." - ".$laclasse['classe']."</strong>.<br />";

                        foreach($eleves as $eleve) {
                            print_r($eleve);
                            echo "<br />";
                        }
                        
                        $db = null;
                    } catch (PDOException $e) {
                        print "Erreur !: " . $e->getMessage() . "<br/>";
                        die();
                    }
                } // if(!empty($_POST['sms']) || !empty($_POST['sms']))

            }// isset $_POST['sms']
        
        ?>
    </body>
</html>
<><><><><><><>
