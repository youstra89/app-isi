<?php

namespace ISI\ORGBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use ISI\ORGBundle\Entity\Vendredi;
use ISI\ORGBundle\Form\VendrediType;
use ISI\ISIBundle\Entity\Anneescolaire;
use ISI\ORGBundle\Repository\VendrediRepository;
use ISI\ISIBundle\Repository\AnneeContratRepository;

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
    $vendredis   = $repoVendredi->findAll();

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
}
