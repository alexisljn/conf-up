<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $conferenceRepository;
    private $paginator;

    private function getAverage(Conference $conference)
    {
        $conference->getVotes();
        $votes = $conference->getVotes();
        $values = [];
        foreach ($votes as $singleVote) {
            $values[] = $singleVote->getValue();
        }
        $average = round(array_sum($values) / count($values), 2);

        return $average;
    }

    public function __construct(ConferenceRepository $conferenceRepository, PaginatorInterface $paginator)
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/", name="home")
     */
    public function getConferences(PaginatorInterface $paginator, Request $request) // 3 by 3
    {
        $conferences = [];
        $conf = $this->conferenceRepository->findAll();

        foreach ($conf as $conference) {
            $average = $this->getAverage($conference);
            $conferences[] = [
                'conference' => $conference,
                'average' => $average
            ];
        }

        $pagination = $this->paginator->paginate(
            $conferences, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            3 /*limit per page*/
        );

        return $this->render('home/index.html.twig', [
            'conferences' => $pagination
        ]);
    }
}
