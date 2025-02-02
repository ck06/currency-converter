<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Entity\User;
use App\Entity\Whitelist;
use App\Service\Inverter;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    private ObjectManager $manager;

    public const REFERENCE_PREFIX = 'user_';

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->createAdmin();
        $this->createUser();

        $manager->flush();
    }

    private function createUser(): void
    {
        $user = (new User())
            ->setUsername('user')
            ->setPassword('picobello')
            ->setRoles([]);

        echo "Created user with username 'user' and password 'picobello'".PHP_EOL;

        $this->manager->persist($user);
        $this->addReference(self::REFERENCE_PREFIX.'user', $user);
    }

    private function createAdmin(): void
    {
        $admin = (new User())
            ->setUsername('admin')
            ->setPassword('picobello')
            ->setRoles([]);

        echo "Created user with username 'admin' and password 'picobello'".PHP_EOL;

        $this->manager->persist($admin);
        $this->addReference(self::REFERENCE_PREFIX.'admin', $admin);
    }
}
