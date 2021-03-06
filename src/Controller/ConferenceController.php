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

    private function getAverage(Conference $conference)
    {
        $conference->getVotes();
        $votes = $conference->getVotes();
        if(count($votes) < 1) {
            return 'not voted by anyone yet';
        }
        $values = [];
        foreach ($votes as $singleVote) {
            $values[] = $singleVote->getValue();
        }
        $average = round(array_sum($values) / count($values), 2);

        return $average;
    }

    /**
     * @Route("/conference/{id}", name="conference_vote")
     */
    public function getConference(Conference $conference, Request $request, EntityManagerInterface $em)
    {
        $average = $this->getAverage($conference);

        $user = $this->getUser();
        $alreadyVotedMessage = null;

        foreach ($user->getVotes() as $userVote) {
            foreach ($conference->getVotes() as $confVote) {
                if($userVote === $confVote) {
                    $alreadyVotedMessage = 'You already voted for this conference';
                }
            }
        }

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


        return $this->render('conference/conference.html.twig', [
            'form' => $form->createView(),
            'conference' => $conference,
            'average' => $average,
            'message' => $alreadyVotedMessage
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
        $averages = [];

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

        // SI L'USER N'A JAMAIS VOTE

        if (empty($votedConferences)){
            foreach ($allConferences as $conference) {
                $average = $this->getAverage($conference);
                $averages[] = $average;
                $unvotedConferences[] = [
                    'conference' => $conference,
                    'average' => $average
                ];

            }
            //dd('totot');
            return $this->render('conference/unvoted.html.twig', [
                'conferences' => $unvotedConferences
            ]);
        }

        // SI L'USER A DEJA VOTE SUR AU MOINS UNE CONFERENCE

        foreach ($votedConferences as $conference) {
            $id[] = $conference->getId();
        }

        $unvoted = $conferenceRepository->createQueryBuilder('c')
            ->where('c.id NOT IN (:id)')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;

        foreach ($unvoted as $conference) {
            $average = $this->getAverage($conference);
            $unvotedConferences[] = [
                'conference' => $conference,
                'average' => $average
            ];

        }

        return $this->render('conference/unvoted.html.twig', [
            'conferences' => $unvotedConferences
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
                        $average = $this->getAverage($conference);
                        $votedConferences[] = [
                            'conference' => $conference,
                            'average' => $average
                        ];
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
        $conferences = [];
        $userInput = $request->request->get('search');
        $conf = $conferenceRepository->createQueryBuilder('c')
                                          ->where('c.name LIKE :name')
                                          ->setParameter('name', '%'.$userInput.'%')
                                          ->getQuery()
                                          ->getResult()
                                          ;
        foreach ($conf as $conference) {
           $average = $this->getAverage($conference);
           $conferences[] = [
               'conference' => $conference,
               'average' => $average
           ];
        }
        return $this->render('conference/searched.html.twig', [
            'conferences' => $conferences
        ]);
    }
}
