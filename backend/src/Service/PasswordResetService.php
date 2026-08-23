<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Handles the "forgot password" flow: generates a one-time reset token,
 * emails a reset link, then applies the new password once the token is used.
 */
class PasswordResetService
{
    private const TOKEN_TTL = '+1 hour';

    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer,
        private string $frontendUrl,
        private string $mailerFrom,
    ) {}

    /**
     * Generates a reset token for the given email and sends the reset link.
     * Returns the raw token, or null when no account matches the email
     * (so callers cannot enumerate registered accounts).
     */
    public function request(string $email): ?string
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));

        $user->setResetToken(hash('sha256', $token));
        $user->setResetTokenExpiresAt(new \DateTimeImmutable(self::TOKEN_TTL));

        $this->mailer->send($this->buildEmail($user, $token));
        $this->em->flush();

        return $token;
    }

    /**
     * Applies the new password for the user owning the token.
     *
     * @throws \InvalidArgumentException when the token is unknown or expired
     */
    public function reset(string $token, string $newPassword): void
    {
        $users = $this->userRepository->findAllWithActiveResetToken(new \DateTimeImmutable());

        foreach ($users as $user) {
            if (hash_equals($user->getResetToken(), hash('sha256', $token))) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
                $user->setResetToken(null);
                $user->setResetTokenExpiresAt(null);
                $this->em->flush();

                return;
            }
        }

        throw new \InvalidArgumentException('Invalid or expired reset token');
    }

    private function buildEmail(User $user, string $token): Email
    {
        $resetUrl = sprintf('%s/reset-password?token=%s', rtrim($this->frontendUrl, '/'), $token);

        return (new Email())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Réinitialiser votre mot de passe Watchly')
            ->text(sprintf(
                "Bonjour %s,\n\nVous avez demandé la réinitialisation de votre mot de passe.\n\nCliquez sur ce lien pour définir un nouveau mot de passe :\n%s\n\nCe lien expire dans une heure. Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.\n\n— L'équipe Watchly",
                $user->getUsername(),
                $resetUrl
            ));
    }
}
