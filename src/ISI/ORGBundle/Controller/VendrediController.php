<?php

namespace ISI\ORGBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use ISI\ORGBundle\Entity\Vendredi;
use ISI\ORGBundle\Form\VendrediType;
use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ORGBundle\Repository\VendrediRepository;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class VendrediController extends Controller
{
  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function indexAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee    = $em->getRepository('ISIBundle:Anneescolaire');
    $repoVendredi = $em->getRepository('ORGBundle:Vendredi');
    $annee       = $repoAnnee->find($as);
    $requete_des_vendredis = "SELECT v.id, v.date, i.nom AS imam_nom, i.pnom AS imam_pnom, m.nom AS mosquee_nom, c.nom AS commune_nom FROM vendredi v JOIN imam i ON v.imam = i.id JOIN mosquee m ON m.id = v.mosquee JOIN commune c ON c.id = m.commune_id ORDER BY v.id DESC;";
    $statement = $em->getConnection()->prepare($requete_des_vendredis);
    $statement->execute();
    $vendredis = $statement->fetchAll();

    return $this->render('ORGBundle:Vendredi:index.html.twig', [
      'asec'  => $as,
      'annee' => $annee,
      'vendredis' => $vendredis,
    ]);
  }

  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function addAction(Request $request, $as)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee    = $em->getRepository('ISIBundle:Anneescolaire');
    $repoVendredi = $em->getRepository('ORGBundle:Vendredi');
    $annee       = $repoAnnee->find($as);
    $vendredi = new Vendredi();
    $form = $this->createForm(VendrediType::class, $vendredi);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid())
    {
      $date = $request->request->all()["date"];
      $good_format = strtotime ($date);
      if(date('N', $good_format) != 5)
      {
        $request->getSession()->getFlashBag()->add('error', 'La date que vous avez sélectionnée ne correspond pas à un vendredi!');
        return $this->redirectToRoute('vendredi.add', ['as' => $as]);
      }
      elseif($date < (new \DateTime())->format('Y-m-d')){
        $request->getSession()->getFlashBag()->add('error', 'La date que vous avez sélectionnée est déjà passée!');
        return $this->redirectToRoute('vendredi.add', ['as' => $as]);
      }
      else{
        $imamId    = $vendredi->getImam()->getId();
        $mosqueeId = $vendredi->getMosquee()->getId();
        $mosquee = $em->getRepository('ORGBundle:Mosquee')->find($mosqueeId);
        if(in_array(2, $mosquee->getOptions()))
        {
          $request->getSession()->getFlashBag()->add('error', 'La programmation des offices du vendredi n\'est pas prise en compte dans cette mosquée!');
          return $this->redirectToRoute('vendredi.add', ['as' => $as]);
        }
        // foreach ($mosquee->getOptions() as $key => $value) {
        //   if($value === 2)
        // }
        // return new Response(var_dump($date));
        $vend = $repoVendredi->disponibiliteMosquee($mosqueeId, $date);
        if(!empty($vend)){
          $request->getSession()->getFlashBag()->add('error', 'Il y a déjà un imam prévu dans cette mosquée!');
          return $this->redirectToRoute('vendredi.add', ['as' => $as]);
        }

        $vend = $repoVendredi->disponibiliteImam($imamId, $date);
        if(!empty($vend)){
          // return new Response(var_dump($vend));
          $request->getSession()->getFlashBag()->add('error', 'Cet imam est déjà programmé dans une autre mosquée!');
          return $this->redirectToRoute('vendredi.add', ['as' => $as]);
        }
        $vendredi->setDate(new \DateTime($date));
        $em->persist($vendredi);
        $em->flush();
        $this->addFlash('info', 'La prière a bien été enregistrée.');
        return $this->redirectToRoute('home_vendredis', ['as' => $as]);
      }
    }
    return $this->render('ORGBundle:Vendredi:vendredi-add.html.twig', [
      'form' => $form->createView(),
      'asec'  => $as,
      'annee' => $annee,
    ]);
  }


  /**
   * @Security("has_role('ROLE_ORGANISATION')")
   */
  public function editAction(Request $request, $as, $id)
  {
    $em = $this->getDoctrine()->getManager();
    $repoAnnee    = $em->getRepository('ISIBundle:Anneescolaire');
    $repoVendredi = $em->getRepository('ORGBundle:Vendredi');
    $annee       = $repoAnnee->find($as);
    // $requete_du_vendredi = "SELECT * FROM vendredi;";
    // $statement = $em->getConnection()->prepare($requete_du_vendredi);
    // $statement->execute();
    $vendredi = $repoVendredi->find($id);

    $form = $this->createForm(VendrediType::class, $vendredi);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid())
    {
      $date = $request->request->all()["date"];
      $good_format = strtotime ($date);
      if(date('N', $good_format) != 5)
      {
        $request->getSession()->getFlashBag()->add('error', 'La date que vous avez sélectionnée ne correspond pas à un vendredi!');
        return $this->redirectToRoute('vendredi.add', ['as' => $as]);
      }
      elseif($date < (new \DateTime())->format('Y-m-d')){
        $request->getSession()->getFlashBag()->add('error', 'La date que vous avez sélectionnée est déjà passée!');
        return $this->redirectToRoute('vendredi.add', ['as' => $as]);
      }
      else{
        $imamId    = $vendredi->getImam()->getId();
        $mosqueeId = $vendredi->getMosquee()->getId();
        $mosquee = $em->getRepository('ORGBundle:Mosquee')->find($mosqueeId);
        if(in_array(2, $mosquee->getOptions()))
        {
          $request->getSession()->getFlashBag()->add('error', 'La programmation des offices du vendredi n\'est pas prise en compte dans cette mosquée!');
          return $this->redirectToRoute('vendredi.add', ['as' => $as]);
        }
        // foreach ($mosquee->getOptions() as $key => $value) {
        //   if($value === 2)
        // }
        // return new Response(var_dump($date));
        $vend = $repoVendredi->disponibiliteMosquee($mosqueeId, $date);
        if(!empty($vend)){
          $request->getSession()->getFlashBag()->add('error', 'Il y a déjà un imam prévu dans cette mosquée!');
          return $this->redirectToRoute('vendredi.add', ['as' => $as]);
        }

        $vend = $repoVendredi->disponibiliteImam($imamId, $date);
        if(!empty($vend)){
          // return new Response(var_dump($vend));
          $request->getSession()->getFlashBag()->add('error', 'Cet imam est déjà programmé dans une autre mosquée!');
          return $this->redirectToRoute('vendredi.add', ['as' => $as]);
        }
        $vendredi->setDate(new \DateTime($date));
        $vendredi->setUpdatedAt(new \DateTime($date));
        $em->flush();
        $this->addFlash('info', 'Les modifications ont bien été enregistrées.');
        return $this->redirectToRoute('home_vendredis', ['as' => $as]);
      }
    }
    return $this->render('ORGBundle:Vendredi:vendredi-edit.html.twig', [
      'form' => $form->createView(),
      'asec'  => $as,
      'annee' => $annee,
    ]);
  }
}
