<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $demo = new User();
        $demo->setEmail('demo@sfbois.com');
        $demo->setRoles(['ROLE_MEMBRE']);
        $demo->setPassword($this->passwordHasher->hashPassword($demo, 'demo'));

        $manager->persist($demo);
        $manager->flush();
    }
}
