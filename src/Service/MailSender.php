<?php


namespace App\Service;


use App\Entity\Conference;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use Swift_Mailer;

class MailSender
{
    private $userRepository;
    private $mailer;
    private $container;

    public function __construct(UserRepository $userRepository, Swift_Mailer $mailer, ContainerInterface $container)
    {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->container = $container;
    }

    public function sendMail(Conference $conference)
    {
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $emails[] = $user->getEmail();
        }
        //dd($emails);

        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('admin@confup.com')
            ->setTo($emails)
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'email/index.html.twig',
                    ['conference' => $conference]
                ),
                'text/html'
            );
        $this->mailer->send($message);
        //dd($sent);
    }

    protected function renderView(string $view, array $parameters = []): string
    {
        if ($this->container->has('templating')) {
            @trigger_error('Using the "templating" service is deprecated since version 4.3 and will be removed in 5.0; use Twig instead.', E_USER_DEPRECATED);

            return $this->container->get('templating')->render($view, $parameters);
        }

        if (!$this->container->has('twig')) {
            throw new \LogicException('You can not use the "renderView" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
        }

        return $this->container->get('twig')->render($view, $parameters);
    }


}