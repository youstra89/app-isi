<?php

namespace ISI\MSGBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\Tools\Pagination\Paginator;

use ISI\MSGBundle\Entity\Message;
use ISI\MSGBundle\Repository\RepositoryMessage;

class ConversationsController extends Controller
{
    public function conversationsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoMessage = $em->getRepository('MSGBundle:Message');
        $repoAnnee    = $em->getRepository('ISIBundle:Annee');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $as = $request->get('as');
        $annexeId = $request->get('annexeId');
        $annee    = $repoAnnee->find($as);
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        $unread = [];
        foreach($users as $user)
        {
            $unread[$user->getId()] = $repoMessage->unReadCount($user->getId());
        }

        return $this->render('MSGBundle:Conversations:index.html.twig', [
            'annee'  => $annee,
            'annexe' => $annexe,
            'users'  => $users,
            'unread' => $unread,
        ]);
    }

    public function showAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $repoMessage = $em->getRepository('MSGBundle:Message');
        $userManager = $this->get('fos_user.user_manager');
        $repoAnnee    = $em->getRepository('ISIBundle:Annee');
        $repoAnnexe = $em->getRepository('ISIBundle:Annexe');
        $as = $request->get('as');
        $annexeId = $request->get('annexeId');
        $annee    = $repoAnnee->find($as);
        $annexe = $repoAnnexe->find($annexeId);
        if(!in_array($annexeId, $this->getUser()->idsAnnexes()) or (in_array($annexeId, $this->getUser()->idsAnnexes()) and $this->getUser()->findAnnexe($annexeId)->getDisabled() == 1)){
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'êtes pas autorisés à exploiter les données de l\'annexe <strong>'.$annexe->getLibelle().'</strong>.');
            return $this->redirect($this->generateUrl('annexes_homepage', ['as' => $as]));
        }
        /**
         * @var $paginator \Knp\Component\Pager\Paginator
         */ 
        $paginator = $this->get('knp_paginator');

        $receiver  = $userManager->findUserBy(['id' => $id]);
        $from = $this->getUser();
        $users = $userManager->findUsers();
        
        // $page = new Paginator($repoMessage->getMessages($from->getId(), $receiver->getId()));
        
        $unread = [];
        foreach($users as $user)
        {
            $unread[$user->getId()] = $repoMessage->unReadCount($user->getId());
        }
        // return new Response(var_dump($unread));

        foreach($unread as $key => $msg)
        {
            if($key == $id)
            {
                $lecture_msg = "SELECT m.id FROM message m WHERE m.from_id = :userId;";
                $statement = $em->getConnection()->prepare($lecture_msg);
                $statement->bindValue('userId', $receiver->getId());
                $statement->execute();
                $msgId = $statement->fetchAll();

                foreach($msgId as $mId)
                {
                    $lire_message = $repoMessage->find($mId);
                    $lire_message->setReadAt(new \Datetime());
                }
                $em->flush();
                unset($unread[$key]);
            }
        }


        $query = $repoMessage->getMessages($from->getId(), $receiver->getId());
        $messages = $paginator->paginate(
            $query,
            $request->query->get('page', 1),
            $request->query->get('limit', 5)

        );
        // $paginator->pa

        // return new Response(var_dump($unread));

        if($request->isMethod('post'))
        {
            $content = $request->request->all()['content'];

            $message = new Message();
            $message->setContent($content);
            $message->setFromId($from);
            $message->setToId($receiver);
            $message->setCreateAt(new \Datetime());

            $em->persist($message);
            $em->flush();

            return $this->redirectToRoute('msg_show_conversations', ['id' => $id, 'as' => $as, 'annexeId' => $annexeId]);
        }
        return $this->render('MSGBundle:Conversations:show.html.twig', [
            'users'  => $users,
            'sendTo' => $receiver,
            'annexe' => $annexe,
            'annee' => $annee,
            'unread' => $unread,
            'messages' => $messages
        ]);
    }
}
