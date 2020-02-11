<?php
    try{
        $bdd = new PDO('mysql:host=op499.myd.infomaniak.com;dbname=op499_drdiallo;charset=utf8', 'op499_manager', 'l@note20$2019');
    }
    catch (Exception $e){
        die('Erreur : ' . $e->getMessage());
    }

    $reponse = $bdd->query('SELECT * FROM note_abidjan n WHERE n.examen_id = 5 AND n.eleve_id IN (SELECT f.eleve_id FROM frequenter_abidjan f WHERE f.classe_id = 397)');

    while ($donnees = $reponse->fetch())
    {
    ?>
        <p>
        <strong>Note</strong> : <?php echo $donnees['id']; ?><br />
        La matiere est : <?php echo $donnees['matiere_id']; ?><br />
        Et la note même est <?php echo $donnees['note']; ?> <br />
    </p>
    <?php
    }

$reponse->closeCursor(); // Termine le traitement de la requête

?>

<table class="table table-bordered les-notes" align="center">
    <tr class="alert-secondary titres-tableau-notes">
        <th class="" width="" align="">المواد</th>
        <th class="" width="7%" align="">الدرجات</th>
        <th class="" width="7%" align="">الوحدات</th>
        <th class="" width="15%" align="">نتيجة الضرب</th>
        <th class="" width="" align="">المدرسون</th>
        <th class="" width="" align="">التقدير</th>
        <th class="" width="" align="">توقيع</th>
    </tr>
    <!-- Dans la boucle des matières, on boucle une fois de plus sur les notes -->
    <?php $i = 0 ?>
    <?php $color = ''; ?>
    <?php $totalCoeff = 0; ?>
    <?php foreach($notes as $note): ?>
        <?php foreach($enseignements as $item): ?>
            <?php if ($note->getMatiere()->getId() == $item->getMatiere()->getId()): ?>
                <?php $color = (($i % 2) == 0) ? "#eeeec0" : "white" ; ?>
                <?php $totalCoeff = $totalCoeff + $item->getCoefficient(); ?>
                <tr class="" bgcolor="<?= $color; ?>">
                    <td class="libelle-matiere"><?= $note->getMatiere()->getLibelle(); ?></td>
                    <td class="valeur-note" with="10%" align="center"><?= $note->getNote(); ?></td>
                    <td class="valeur-note" with="10%" align="center"><?= $item->getCoefficient(); ?></td>
                    <td class="valeur-note" with="10%" align="center"><?= $note->getNote() * $item->getCoefficient(); ?></td>
                    <td class=""></td>
                    <td class="appreciation"><?= $note->getAppreciation()->getAppreciationAr(); ?></td>
                    <td class=""></td>
                </tr>
                <?php $i++; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <!-- Fin de la boucle sur les notes -->
</table>

