<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Entity\Vote;
use App\Form\VoteConferenceType;
use App\Repository\ConferenceRepository;
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
            $votes = $conference->getVotes();
            $values = [];
            foreach ($votes as $singleVote) {
                $values[] = $singleVote->getValue();
            }
            $average = round(array_sum($values) / count($values), 2);
            $conference->setAverage($average);
            $em->persist($vote);
            $em->flush();
            return $this->redirectToRoute('conference_vote',['id' => $conference->getId()]);
        }


        return $this->render('conference/index.html.twig', [
            'form' => $form->createView(),
            'conference' => $conference
        ]);
    }

    /**
     * @Route("/unvoted", name="conference_unvoted")
     *
     */
    public function getUnvotedConferences(ConferenceRepository $conferenceRepository)
    {
        $votedConferences = [];
        $unvotedConferences = [];

        $allConferences = $conferenceRepository->findAll();
        $user = $this->getUser();

        foreach ($allConferences as $conference) {
            foreach ($conference->getVotes() as $singleVote) {
                foreach ($user->getVotes() as $userSingleVote) {
                    if($userSingleVote === $singleVote) {
                        $votedConferences[] = $conference;
                    }
                }
            }
        }

        foreach ($allConferences as $conference) {
            foreach ($votedConferences as $votedConference) {
                if($conference !== $votedConference) {
                    $unvotedConferences[] = $conference;
                }
            }
        }

        return $this->render('conference/unvoted.html.twig', [
            'unvoted' => $unvotedConferences
        ]);
    }

    /**
     * @Route("/voted", name="conference_voted")
     *
     */
    public function getvotedConferences(ConferenceRepository $conferenceRepository)
    {
        $votedConferences = [];

        $allConferences = $conferenceRepository->findAll();
        $user = $this->getUser();

        foreach ($allConferences as $conference) {
            foreach ($conference->getVotes() as $singleVote) {
                foreach ($user->getVotes() as $userSingleVote) {
                    if($userSingleVote === $singleVote) {
                        $votedConferences[] = $conference;
                    }
                }
            }
        }
        return $this->render('conference/voted.html.twig', [
            'voted' => $votedConferences
        ]);
    }

    /**
     * @Route("/search", name="search")
     */
    public function search(Request $request, ConferenceRepository $conferenceRepository)
    {
       $userInput = $request->request->get('search');
       $conferences = $conferenceRepository->createQueryBuilder('c')
                                          ->where('c.name LIKE :name')
                                          ->setParameter('name', '%'.$userInput.'%')
                                          ->getQuery()
                                          ->getResult()
                                          ;

        return $this->render('conference/searched.html.twig', [
            'conferences' => $conferences
        ]);
    }
}
