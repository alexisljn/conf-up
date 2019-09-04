<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminEditUserType extends AbstractType
{
    private $selected;
    private $user;
    private $oldPassword;
    private $isEmpty = false;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // On récupère l'user traité par le formulaire
        $this->user = $options['data'];
        // on récupère son mot de passe
        $this->oldPassword = $this->user->getPassword();
        // Selon le rôle de l'user, on attribue à selected le rôle d'User ou d'Admin
        if($this->user->getRoles() == ['ROLE_USER']) {
            $this->selected = 'ROLE_USER';
        } elseif ($this->user->getRoles() == ['ROLE_ADMIN']) {
            $this->selected = 'ROLE_USER,ROLE_ADMIN';
        }
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class, [
                'required' => false
            ])
            ->add('roles', CollectionType::class, [
                'entry_type' => ChoiceType::class,
                'entry_options' => [
                    'label' => false,
                    'choices' => [
                        'User' => 'ROLE_USER',
                        'Admin' => 'ROLE_USER,ROLE_ADMIN'
                    ],
                    'choice_attr' => function($choice) {
                        if ($choice === $this->selected) {
                            return['selected' => true];
                        } else {
                            return ['selected' => false];
                        }
                    }
                ]
            ])
            ->add('submit', SubmitType::class)
        ;


        // Gestionnaire d'evenement qui avant la validation regarde si les champs mdp du formulaire sont vides
        // S'ils sont vides, alors il les remplis d'un mot de passe temporaire et passe à TRUE le "jeton" isEmpty
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                if(empty($data['password'])) {
                    $data['password'] = 'f4k3P455W0RD';
                    $event->setData($data);
                    $this->isEmpty = true;
                }
            }
        );
        // Gestionnaire d'évènement qui après la validation si le jeton isEmpty est à true va venir redonner à l'user concerné
        // son mot de passe d'origine.
        $builder->addEventListener(FormEvents::POST_SUBMIT,
            function() {
                if($this->isEmpty) {
                    $this->user->setPassword($this->oldPassword);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
