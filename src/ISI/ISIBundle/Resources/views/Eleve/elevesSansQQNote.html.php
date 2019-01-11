<?php
    $db = new PDO("mysql:host=localhost; dbname=database", "root", "");
?>
<!DOCTYPE>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Notes incomplètes</title>
    </head>
    <body>
        <h1>Les élèves à qui il manque quelques notes...</h1>
        <?php

        ?>
        <form action="" name="" method="post">
            <label for="annee">Entrez l'identifiant de l'année:</label>
            <input type="integer" id="annee" name="annee" value="1">
            <br>
            <label for="niveau">Entrez l'identifiant d'un niveau:</label>
            <input type="integer" id="niveau" name="niveau">
            <br>
            <label for="examen">Entrez l'identifiant de l'examen:</label>
            <input type="integer" id="examen" name="examen" value="1">
            <br><br>
            <input type="submit" value=" Afficher les élèves " class="ui primary button" name="sms">
        </form>
        <div>
            <?php

            ?>
        </div>
        <?php
            if(isset($_POST['sms']))
            {
                if(!empty($_POST['annee']) && !empty($_POST['niveau']) && !empty($_POST['examen']))
                {
                    if(!is_numeric($_POST['annee']) || !is_numeric($_POST['niveau']) || !is_numeric($_POST['examen']))
                    {
                        echo"<script>alert('Valeurs d\'identifiant incorrectes!!!')</script>";
                        die();
                    }
                    $annee  = $_POST['annee'];
                    $niveau = $_POST['niveau'];
                    $examen = $_POST['examen'];
                    $nbrEleves = 0;
					          $eleveNotesIncompletes = 0;
                    try {
                        // Sélection des matières du niveau
                        /****/
                            // mysql_connect("localhost", "root", "");
                            // mysql_select_db("database");
                            // $matieres = mysql_query('SELECT m0_.matiere_id AS matiere_id_0, m0_.libelle_matiere AS libelle_matiere_1
                            //                 FROM matiere m0_
                            //                 LEFT JOIN enseignement e1_ ON m0_.matiere_id = e1_.matiere_id
                            //                 LEFT JOIN niveau n2_ ON e1_.niveau_id = n2_.id
                            //                 LEFT JOIN anneescolaire a3_ ON e1_.annee_scolaire_id = a3_.annee_scolaire_id
                            //                 WHERE a3_.annee_scolaire_id = '.$annee.' AND e1_.niveau_id = '.$niveau.'
                            //                 ORDER BY m0_.matiere_id ASC');
                            // mysql_close();
                        /****/
						            // Requete de selection de l'id du niveau saisie
                        $requeteMatiere = 'SELECT m0_.matiere_id AS matiereId
                                            FROM matiere m0_
                                            LEFT JOIN enseignement e1_ ON m0_.matiere_id = e1_.matiere_id
                                            LEFT JOIN niveau n2_ ON e1_.niveau_id = n2_.id
                                            LEFT JOIN anneescolaire a3_ ON e1_.annee_scolaire_id = a3_.annee_scolaire_id
                                            WHERE a3_.annee_scolaire_id = '.$annee.' AND e1_.niveau_id = '.$niveau.'
                                            ORDER BY m0_.matiere_id ASC';
                        $requete    = $db->query($requeteMatiere);
                        $matieres   = $requete->fetchAll();
                        $nbrMatiere = count($matieres);
                        // echo var_dump($matieres);
                        $idsMatieres = [];
                        foreach ($matieres as $mat) {
                            $idsMatieres[] = $mat['matiereId'];
                        }
                        // echo var_dump($idsMatieres);
						            // var_dump($matieres);
                        //Fin de sélection des matières


                        // Sélection des classes du niveau
                        $codeSql = 'SELECT c.classe_id AS id, n.libelle_fr AS niveau, c.libelle_classe_fr AS libelleFr
                                    FROM classe c
                                    JOIN niveau n ON c.niveau_id = n.id AND c.niveau_id = '.$niveau.' AND c.annee_scolaire_id = '.$annee;

                        $requete = $db->query($codeSql);
                        $classes = $requete->fetchAll();

                        $i = 1;
                        echo "<table width='85%' border='1'>";
                        foreach ($classes as $classe) {
                            // Sélection des élèves de la classe
                            $requeteEleve = 'SELECT DISTINCT(e0_.eleve_id) AS id, e0_.matricule AS matricule, e0_.nom_fr AS nom, e0_.pnom_fr AS pnom FROM eleve e0_
                                                INNER JOIN frequenter f1_ ON e0_.eleve_id = f1_.eleve_id
                                                INNER JOIN classe c2_ ON f1_.classe_id = c2_.classe_id
                                                INNER JOIN anneescolaire a3_ ON c2_.annee_scolaire_id = a3_.annee_scolaire_id
                                                WHERE a3_.annee_scolaire_id = '.$annee.' AND c2_.classe_id = '.$classe['id'].'
                                                ORDER BY e0_.nom_fr ASC, e0_.pnom_fr ASC';
                            $requete = $db->query($requeteEleve);
                            $eleves = $requete->fetchAll();
                            echo "<tr><td align='left' widh='20%'><strong>N°".$i."</strong></td><td align='left' widh='30%'><strong>Id classe: ".$classe['id']."</strong></td><td align='left' widh='50%'><strong>Libelle: ".$classe['niveau']." - ".$classe['libelleFr']."</strong></td></tr>";
                            echo "<tr><td colspan='3' align='left'>";
                            echo "<table width='95%' align='center'>";
                            echo "<tr><th align='left'>N°</th><th align='left'>Id Elève</th><th align='left'>Matricule</th><th align='left'>Nom & Prénoms</th><th align='left'>Moyenne</th><th align='left'>Nbr de note</th><th align='left'>Note à créer</th></tr>";
                                $j = 1;
                                $idsDesEleves = [];

                                foreach ($eleves as $elv) {
                                    unset($notes);
                                    $nbrEleves++;
                                    // On va maintenant sélectionner les notes des élèves
                                    $requeteNote = 'SELECT n.matiere_id AS matiereId
                                                    FROM note n WHERE n.examen_id = '.$examen.' AND n.eleve_id = '.$elv['id'];
                                    $codeSql = $db->query($requeteNote);
                                    $notes   = $codeSql->fetchAll();
                                    $nbrNote = count($notes);
                                    // $nbrNote = 0;
                                    // Pour chaque élève, on va sélectionner la moyenne
                                    $requeteMoyenne = 'SELECT e.eleve_id AS id, e.matricule AS matricule, e.nom_fr AS nom, e.pnom_fr AS pnom, m.id AS moyenne_id, m.moyenne AS moyenne
                                                        FROM eleve e
                                                        JOIN moyenne m ON e.eleve_id = m.eleve
                                                        JOIN examen ex ON m.examen = ex.examen_id
                                                        WHERE ex.examen_id = '.$examen.' AND e.eleve_id = '.$elv['id'];
                                    $requete = $db->query($requeteMoyenne);
                                    $moyenne = $requete->fetch();
                                    $sql1  = "";
                                    $success = "";
									                  $date = date_format(new \Datetime(), "Y-m-d H:m:s");
                                    if(empty($moyenne))
									                  {
                                        $moy = "Pas de moyenne";
                                        // // On peut donc inserer les moyennes
                                        // $sql1 = 'INSERT INTO moyenne (eleve, examen, date_save, date_update) VALUES ('.$elv['id'].', '.$examen.', '.$date.', '.$date.')';
                                        // $sql = $db->prepare("INSERT INTO moyenne (eleve, examen, date_save, date_update) VALUES (?, ?, ?, ?)");
                                        // $sql->bindParam(1, $el);
                                        // $sql->bindParam(2, $ex);
                                        // $sql->bindParam(3, $dt);
                                        // $sql->bindParam(4, $dt);
                                        //
                                        // $el = $elv['id'];
                                        // $ex = $examen;
                                        // $dt = $date;
                                        // $sql->execute();
                                        // // $requete = $db->query($sql);
                                        // if($sql)
                                        //     $success = "Gooood!!!";
									                   }
          									         else{
          										          $moy = "Ok";
          									         }

                                    // $pasDeNotes contiendra les matieres où l'élève n'a pas de note $avecNote, où il en a
                                    $result = [];
                                    $idsMatieresNotes = [];
									                  if($nbrNote < $nbrMatiere)
        									          {
        										          $eleveNotesIncompletes++;
                                                $color = "#DCDCDC";
                                                foreach($notes as $note)
                                                {
                                                    $idsMatieresNotes[] = $note['matiereId'];
                                                }
                                                $result = array_diff($idsMatieres, $idsMatieresNotes);
									                             }
        									         else{
        										          $color = "";
                                            }
                                    // // $result contient les ids des matieres où l'élève $elv n'a pas de note
                                    // // On va donc les inserer
                                    // foreach ($result as $res) {
                                    //     $sql = $db->prepare("INSERT INTO note (eleve_id, examen_id, matiere_id, date_save, date_update) VALUES (?, ?, ?, ?, ?)");
                                    //     $sql->bindParam(1, $el);
                                    //     $sql->bindParam(2, $ex);
                                    //     $sql->bindParam(3, $res);
                                    //     $sql->bindParam(4, $dt);
                                    //     $sql->bindParam(5, $dt);
                                    //
                                    //     $el = $elv['id'];
                                    //     $ex = $examen;
                                    //     $dt = $date;
                                    //     try{
                                    //         if(!$sql->execute())
                                    //             throw new RuntimeException("Error Processing Request", 1);
                                    //
                                    //     } catch (PDOException $e) {
                                    //         print "Erreur !: " . $e->getMessage() . "<br/>";
                                    //         die();
                                    //     }
                                    // }
                                    $idsDesEleves[] = $elv['id'];
                                    echo "<tr bgcolor='".$color."'><td align='left'>".$j."</td><td align='left'>".$elv['id']."</td><td align='left'>".$elv['matricule']."</td><td align='left'>".$elv['nom']." ".$elv['pnom']."<br />".$sql1."<br />".$success."</td><td align='left'>".$moy."</td><td align='left'>".$nbrNote." / ".$nbrMatiere."</td><td align='left'>".implode(', ', $result)."</td></tr>";
                                    $j++;
                                }// Fin foreach $eleves as $elv
                            echo "<tr><td colspan='6'>".implode(', ', $idsDesEleves)."</td></tr>";
                            echo "</table>";
                            echo "</td></tr>";
                            $i++;
                        }// Fin foreach $classes as $classe
                        echo "</table>";

                        echo "Le nombre total d'élèves est : <strong>".$nbrEleves."</strong><br />";
						            echo "Et <strong>".$eleveNotesIncompletes."</strong> qui n'ont pas les notes au complet.";


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
<><><><><>
