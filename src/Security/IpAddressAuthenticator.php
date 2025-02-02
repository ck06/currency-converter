<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AddressValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class IpAddressAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UrlGeneratorInterface $urlGenerator,
        private AddressValidator $validator,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return ($request->request->get('_username') ?? '') !== '';
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // fuck it, we'll just force it with a 500 error
        throw new \RuntimeException($exception->getMessage());
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    public function authenticate(Request $request): Passport
    {
        /** @var UserRepository $repository */
        $repository = $this->em->getRepository(User::class);
        $username = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        $user = $repository->findOneBy(['username' => $username]);
        if (!$user instanceof User) {
            throw new CustomUserMessageAuthenticationException('User not found.');
        }

        $ip = $request->getClientIp();
        if (!$this->isIpInWhitelist($ip, $user)) {
            throw new CustomUserMessageAuthenticationException(sprintf(
                'You do not have permission to log in from %s', $ip
            ));
        }

        return new Passport(
            new UserBadge($username, fn() => $user),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    private function isIpInWhitelist(string $ip, User $user): bool
    {
        foreach ($user->getWhitelist() as $whitelistItem) {
            $cidr = $this->validator->determineCidr($whitelistItem->getAddress());
            $chunksToCompare = $cidr / 8;

            $ipChunks = explode('.', $ip, 4);
            $whitelistChunks = explode('.', $whitelistItem->getAddress(), 4);
            $matches = 0;
            for ($i = 0; $i < $chunksToCompare; $i++) {
                if ($ipChunks[$i] === $whitelistChunks[$i]) {
                    $matches++;
                    continue;
                }

                break;
            }

            if ($matches === $chunksToCompare) {
                return true;
            }
        }

        return false;
    }
}
