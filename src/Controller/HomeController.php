<?php

namespace App\Controller;

use App\Repository\ConferenceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    private $conferenceRepository;
    private $paginator;

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
        //$conferences = $this->conferenceRepository->findBy([], [],3,$offset);
        $conferences = $this->conferenceRepository->findAll();
        //$nbOfPages = count($allConferences) / 3;
        //dd($nbOfPages);
        //$paginator  = $this->get('knp_paginator');
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
