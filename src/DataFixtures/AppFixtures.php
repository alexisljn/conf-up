<?php

namespace App\DataFixtures;

use App\Entity\Conference;
use App\Entity\User;
use App\Entity\Vote;
use App\Repository\ConferenceRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{

    private $userRepository;
    private $conferenceRepository;

    public function __construct(UserRepository $userRepository, ConferenceRepository $conferenceRepository)
    {
        $this->userRepository = $userRepository;
        $this->conferenceRepository = $conferenceRepository;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setEmail($faker->email);
            $user->setPassword($faker->regexify('[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}'));
            $user->setRoles(['ROLE_USER']);
            $user->setApiKey($faker->regexify('[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}'));
            $manager->persist($user);
        }

        for($i = 0; $i < 10; $i++) {
            $conf = new Conference();
            $conf->setAverage(rand(0,5));
            $conf->setDescription($faker->text(200));
            $conf->setName($faker->text(50));
            $manager->persist($conf);
        }

        $manager->flush();

        for($i = 0; $i < 100; $i++) {
            $vote = new Vote();
            $vote->setValue(rand(0,5));
            $randomUserId = rand(1,5);
            $randomUser = $this->userRepository->findOneBy(['id' => $randomUserId]);
           // dd($randomUser);
            $vote->setUser($randomUser);
            $randomUser->addVote($vote);
            $randomConfId = rand(1,10);
            $randomConf = $this->conferenceRepository->findOneBy(['id' => $randomConfId]);
            $vote->setConference($randomConf);
            $randomConf->addVote($vote);
            $manager->persist($vote);
        }

        $manager->flush();
    }
}
