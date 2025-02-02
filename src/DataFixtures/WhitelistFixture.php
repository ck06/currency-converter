<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Entity\User;
use App\Entity\Whitelist;
use App\Service\Inverter;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WhitelistFixture extends Fixture implements DependentFixtureInterface
{
    private ObjectManager $manager;

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        foreach (['user', 'admin'] as $username) {
            $user = $this->getReference(UserFixture::REFERENCE_PREFIX.$username, User::class);
            $this->createWhitelist($user);
            $this->manager->persist($user);
        }

        $manager->flush();
    }

    private function createWhitelist(User $user): void
    {
        $addresses = ['127.0.0.1', '172.18'];
        foreach ($addresses as $address) {
            $whitelist = (new Whitelist())->setUser($user)->setAddress($address);
            $this->manager->persist($whitelist);
        }
    }
}
