<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\GroomerProfile;
use App\Entity\User;
use App\Repository\ReviewRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, GroomerProfile>
 */
final class ReviewIntegrityVoter extends Voter
{
    public const CREATE = 'REVIEW_CREATE';

    public function __construct(private ReviewRepository $reviewRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::CREATE === $attribute && $subject instanceof GroomerProfile;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $groomerUser = $subject->getUser();
        if (null !== $groomerUser && $groomerUser->getId() === $user->getId()) {
            return false;
        }

        $existing = $this->reviewRepository->findOneBy([
            'groomer' => $subject,
            'author' => $user,
        ]);

        return null === $existing;
    }
}
