<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:user:create', description: 'Create a user')]
class UserCreateTask extends Command
{
    private SymfonyStyle $io;

    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('User creation task');

        $username = $this->getUsername();
        $user = $this->getUser($username);
        if ($user instanceof User && !$this->confirmOverwrite()) {
            return self::SUCCESS;
        }

        $password = $this->getPassword();
        $roles = $this->getAdminPermission() ? ['ROLE_ADMIN'] : [];

        $this->createOrUpdateUser($username, $password, $roles, $user);

        return self::SUCCESS;
    }

    private function getUsername(): string
    {
        return $this->io->ask(
            question: 'Username',
            validator: static function (?string $answer) {
                $answer = mb_trim($answer);
                if (!$answer) {
                    throw new RuntimeException('You must enter a value');
                }

                return $answer;
            }
        );
    }

    private function confirmOverwrite(): bool
    {
        return $this->io->confirm('This user already exists, do you want to update it?');
    }

    private function getPassword(): string
    {
        return $this->io->askHidden(
            question: 'Password',
            validator: static function (?string $answer) {
                if (!$answer) {
                    throw new RuntimeException('You must enter a value');
                }

                return $answer;
            }
        );
    }

    private function getAdminPermission(): bool
    {
        // we use ask() over confirm() here for validation purposes; you MUST write the answer in full.
        $answer = $this->io->ask(
            question: 'Is this an admin user?',
            default: 'No',
        );

        $answer = mb_trim($answer);
        $answer = mb_strtolower($answer);
        if ($answer === 'yes') {
            return true;
        }

        if ($answer === 'no') {
            return false;
        }

        $this->io->warning('You must enter your answer in full.');

        return $this->getAdminPermission();
    }

    private function getUser(string $username): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    private function createOrUpdateUser(string $username, string $password, array $roles, ?User $user): void
    {
        if (!$user instanceof User) {
            $user = (new User())->setUsername($username);
        }

        $user
            ->setPassword($password)
            ->setRoles($roles);

        $this->em->persist($user);
        $this->em->flush();
    }
}
