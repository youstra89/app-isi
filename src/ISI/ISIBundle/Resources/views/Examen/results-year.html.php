<?php

  $db = new PDO("mysql:host=localhost; dbname=database", "root", "");

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <form class="" action="" method="post">
      <label>Choisir la classe</label>
      <select name="classe">
        <option value="">Choisir une classe</option>
        <?php
          $req = $db->prepare("SELECT c.classe_id, c.libelle_classe_fr, n.libelle_fr FROM classe c JOIN niveau n ON c.niveau_id = n.id WHERE n.groupe_formation_id = 2");
          $req->execute();
          $rows = $req->fetchall();
          foreach ($rows as $row) {
            echo"<option value='".$row[0]."'>".$row[2]." - ".$row[1]."</option>";
          }
        ?>
      </select><br>
      <label>Choisir l'année scolaire</label>
      <select name="annee">
        <option value="">Choisir une année</option>
        <?php
          $req = $db->prepare("SELECT * FROM anneescolaire");
          $req->execute();
          $rows = $req->fetchall();
          foreach ($rows as $row) {
            echo"<option value='".$row[0]."'>".$row[1]."</option>";
          }
        ?>
      </select><br>
      <input type="submit" name="sms" value="   Valider   ">
    </form>

    <?php

      function admission($nmsr, $nmr)
      {
        if ($nmsr >= 3) {
          # code...
          $decision = 0;
        }
        elseif ($nmsr == 2 && $nmr > 0) {
          # code...
          $decision = 0;
        }
        elseif ($nmsr == 1 && $nmr >= 3) {
          # code...
          $decision = 0;
        }
        elseif ($nmr >= 5) {
          # code...
          $decision = 0;
        }
        else {
          # code...
          $decision = 1;
        }

        return $decision;
      }

      if(isset($_POST['sms']))
      {

        if(empty($_POST['classe']) || empty($_POST['annee']))
        {
          echo "Valeurs incorrectes. Faites des sélections!!!";
        }

        else {
          # code...
          $req = $db->prepare("SELECT e.eleve_id, e.matricule, e.nom_fr, e.pnom_fr, e.sexe_fr, f.id
                                FROM eleve e
                                JOIN frequenter f ON e.eleve_id = f.eleve_id
                                JOIN classe c ON c.classe_id = f.classe_id WHERE c.classe_id = :classeId AND f.annee_scolaire_id = :annee");
          $req->execute(array(
          'classeId' => $_POST['classe'], 'annee' => $_POST['annee']));
          $eleves = $req->fetchall();

          $req = $db->prepare("SELECT m.matiere_id, m.libelle_matiere, e.statu_matiere
                              FROM classe c
                              JOIN niveau n ON c.niveau_id = n.id
                              JOIN enseignement e ON e.niveau_id = n.id
                              JOIN matiere m ON m.matiere_id = e.matiere_id
                              WHERE c.classe_id = :classe AND e.annee_scolaire_id = :annee ORDER BY m.matiere_id ASC");
          $req->execute(array(
          'classe' => $_POST['classe'], 'annee' => $_POST['annee']));
          $matieres = $req->fetchall();

          echo "<table border='1'>";
          echo "<thead>";
          echo "<tr><th>N°</th>";
          echo "<th>Id</th>";
          echo "<th>Matricule</th>";
          echo "<th>Nom & prénoms</th>";
          foreach ($matieres as $matiere) {
            # code...
            echo "<th>".$matiere[1]."</th>";
          }
          echo "<th>MS recalées</th>";
          echo "<th>MNS recalées</th>";
          echo "<th>Admission</th></tr>";
          echo "</thead>";
          echo "<tboby>";

          $num = 0;
          foreach ($eleves as $eleve) {
            $num++;
            $msr  = 0;
            $mnsr = 0;
            echo "<tr><td>".$num."</td>";
            echo "<td>".$eleve[0]."</td>";
            echo "<td>".$eleve[1]."</td>";
            echo "<td>".$eleve[2]." ".$eleve[3]."</td>";
            foreach ($matieres as $matiere) {
              /*
              Pour chaque matière, on va sélectionner la moyenne annuelle
              */
              $req = $db->prepare("SELECT e.eleve_id, f.id, fm.matiere, fm.moyenne, fm.validation
                                  FROM eleve e
                                  JOIN frequenter f ON e.eleve_id = f.eleve_id
                                  JOIN frequenter_matiere fm ON f.id = fm.frequenter
                                  WHERE e.eleve_id = :eleve and fm.matiere = :matiere");
              $req->execute(array(
              'eleve' => $eleve[0], 'matiere' => $matiere[0]));
              $moyennes = $req->fetchall();
              // echo "<pre>";
              // print_r($moyennes);
              // echo "</pre>";
              $color = "";
              foreach ($moyennes as $moyenne) {
                # code...
                if($matiere[0] == $moyenne[2] and $matiere[2] == true and $eleve[0] == $moyenne[0] and $eleve[5] == $moyenne[1])
                {

                  // $color = ($moyenne[2] == 1) ? "red" : "";
                  $color = "red";
                  $moyenne[3] < 11 ? $msr++ : $msr ;
                  echo "<td bgcolor='".$color."'>".$moyenne[3]."</td>";
                }
                elseif($matiere[0] == $moyenne[2] and $matiere[2] == false and $eleve[0] == $moyenne[0] and $eleve[5] == $moyenne[1])
                {
                  $moyenne[3] < 11 ? $mnsr++ : $mnsr ;
                  echo "<td>".$moyenne[3]."</td>";
                }
              }
            }

            $admission = admission($msr, $mnsr);
            // $admission = "OK";
            echo "<td>".$msr."</td>";
            echo "<td>".$mnsr."</td>";
            echo "<td>".$admission."</td></tr>";
          }

          echo "</tboby>";
          echo "</table>";
        }

      }

    ?>
  </body>
</html>
