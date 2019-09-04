<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Form\CreateConferenceType;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/conference", name="admin_conferences")
     */
    public function getConferences(ConferenceRepository $conferenceRepository)
    {
        $conferences = $conferenceRepository->findAll();

        return $this->render('admin/conferences.html.twig', [
            'conferences' => $conferences,
        ]);
    }

    /**
     * @Route("/admin/conference/{id}", name="admin_conference")
     */
    public function getConference(Conference $conference)
    {
        return $this->render('admin/conference.html.twig', [
            'conference' => $conference
        ]);
    }


    /**
     * @Route("/admin/create/conference", name="create_conference")
     */
    public function createConference(Request $request, EntityManagerInterface $em)
    {
        $conference = new Conference();
        $form = $this->createForm(CreateConferenceType::class, $conference);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($conference);
            $em->flush();
            return $this->redirectToRoute('admin_conference',['id' => $conference->getId()]);
        }

        return $this->render('admin/create-conf.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/edit/conference/{id}", name="edit_conference")
     */
    public function editConference(Conference $conference, EntityManagerInterface $em, Request $request)
    {
        $form = $this->createForm(CreateConferenceType::class, $conference);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_conference',['id' => $conference->getId()]);
        }

        return $this->render('admin/edit-conf.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/delete/conference/{id}", name="delete_conference")
     */
    public function deleteConference(Conference $conference, EntityManagerInterface $em)
    {
        $em->remove($conference);
        $em->flush();

        return $this->redirectToRoute('admin_conferences');
    }
}
