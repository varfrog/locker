<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordEncoderInterface $encoder)
    {
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('john');
        $user->setPassword($this->encoder->encodePassword($user, 'maxsecure'));

        $manager->persist($user);
         
        $manager->flush();
    }
}
