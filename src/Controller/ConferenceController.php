<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Entity\Vote;
use App\Form\VoteConferenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    /**
     * @Route("/conference/{id}", name="conference_vote")
     */
    public function getConference(Conference $conference, Request $request, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $vote = new Vote();
        $form = $this->createForm(VoteConferenceType::class,$vote);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $vote->setUser($user);
            $vote->setConference($conference);
            $user->addVote($vote);
            $conference->addVote($vote);
            $em->persist($vote);
            $em->flush();
            return $this->redirectToRoute('conference_vote',['id' => $conference->getId()]);
        }


        return $this->render('conference/index.html.twig', [
            'form' => $form->createView(),
            'conference' => $conference
        ]);
    }
}
