<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Entity\Whitelist;
use App\Service\AddressValidator;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:user:whitelist', description: "Alter a user's whitelist")]
class UserConfigureTask extends Command
{
    private SymfonyStyle $io;

    private array $addressList = [];

    public function __construct(
        private EntityManagerInterface $em,
        private AddressValidator $addressValidator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Whitelist configuration');

        $username = $this->askUsername();
        $user = $this->getUser($username);
        if (!$user instanceof User) {
            throw new RuntimeException('Unable to find user');
        }

        $this->addressList = [];
        foreach ($user->getWhitelist() as $whitelist) {
            $this->addressList[] = $whitelist->getAddress();
        }

        $this->askAddresses();
        $this->updateAddresses($user);

        return self::SUCCESS;
    }

    private function askUsername(): string
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

    private function askAddresses(): void
    {
        // i aint typing these several times lol
        $addChoice = 'Add an address';
        $dropChoice = 'Remove an address';
        $exitChoice = 'Nothing';

        $choice = $this->io->choice(
            'What do you want to do?',
            [$addChoice, $dropChoice, $exitChoice],
            $exitChoice,
            false,
        );

        if ($choice === $exitChoice) {
            return;
        }

        $choice === $addChoice ?
            $this->askAddAddress() :
            $this->askRemoveAddress();

        $this->askAddresses();
    }

    private function askAddAddress(): void
    {
        $address = $this->io->ask(
            question: <<<'Question'
Please enter the IPv4 address(es) this user may connect from.
CIDR is accepted, but only /24, /16 and /0

Enter nothing when you're done.
Question,
            default: '',
            validator: fn(string $answer) => $this->addressValidator->validate($answer),
        );

        if ($address === '') {
            return;
        }

        $this->addressList[] = $address;

        $this->askAddAddress();
    }

    private function askRemoveAddress(): void
    {
        $exitChoice = 'None';
        $choices = $this->io->choice(
            question: 'Select the addresses you wish to remove.',
            choices: array_merge($this->addressList, [$exitChoice]),
            default: $exitChoice,
            multiSelect: true
        );

        if (count($choices) === 1 && $choices[0] === $exitChoice) {
            return;
        }

        // remove our exit choice if present in the list of choices, to prevent it getting into our final list
        $exitPos = array_search($exitChoice, $choices, true);
        if ($exitPos !== false) {
            unset($choices[$exitPos]);
        }

        $this->addressList = array_diff($this->addressList, $choices);
    }

    private function getUser(string $username): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    private function updateAddresses(User $user): void
    {
        // this "update" is more of a "purge existing items and re-insert everything"
        // we could do this smarter, but that would take more time than its worth.
        if ($user->getWhitelist()->count() > 0) {
            foreach ($user->getWhitelist() as $existingWhitelist) {
                $user->removeWhitelist($existingWhitelist);
                $this->em->remove($existingWhitelist);
            }

            $this->em->flush();
        }

        foreach ($this->addressList as $address) {
            $whitelist = (new Whitelist())->setUser($user)->setAddress($address);
            $user->addWhitelist($whitelist);
            $this->em->persist($whitelist);
        }

        $this->em->persist($user);
        $this->em->flush();
    }
}
