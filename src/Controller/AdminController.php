<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Entity\User;
use App\Form\AdminCreateUserType;
use App\Form\AdminEditUserType;
use App\Form\CreateConferenceType;
use App\Repository\ConferenceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

    /**
     * @Route("/admin/users", name="admin_users")
     */
    public function getUsers(UserRepository $userRepository)
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/admin/users/{id}", name="admin_user")
     */
    public function getSinglesUser(User $user)
    {
        return $this->render('admin/user.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("admin/create/user", name="create_user")
     */
    public function addUser(Request $request, EntityManagerInterface $em)
    {
        $user = new User();
        $form = $this->createForm(AdminCreateUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('admin_user', ['id' => $user->getId()]);
        }

        return $this->render('admin/create-user.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/edit/users/{id}", name="edit_user")
     */
    public function editUser(User $user, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        // On stocke le mot de passe avant validation du formulaire dans une variable
        $oldPassword = $user->getPassword();

        // On récupère les roles de l'utilisateur et si c'est un administrateur et qu'il
        // en a donc 2, on supprimer 'ROLE_USER' Afin de n'avoir que 'ROLE_ADMIN'
        $roles = $user->getRoles();

        if(count($roles) > 1) {
            $roles = array_slice($roles, 1);
            $user->setRoles($roles);
        }

        $form = $this->createForm(AdminEditUserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // On récupère les entrées du formulaires
            $data = $form->getData();
            // on récupère le mot de passe parmis les entrées
            $password = ($data->getpassword());

            // On compare le nouveau mot de passe avec l'ancien
            // Si c'est le même on procède à une modification sans altération du mot de passe
            if($password == $oldPassword) {
                $em->flush();
            } else {
                $pass = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($pass);
           $em->flush();
            }
            return $this->redirectToRoute('admin_user', ['id' => $user->getId()]);
        }

        return $this->render('admin/edit-user.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/delete/users/{id}", name="delete_user")
     */
    public function deleteUser(User $user, EntityManagerInterface $em)
    {
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('admin_users');
    }

}
