<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TaskVoter extends Voter
{
    const TOGGLE = 'toggle';
    const DELETE = 'delete';
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::DELETE, self::TOGGLE])) {
            return false;
        }

        // only vote on `Task` objects
        if (!$subject instanceof Task) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN') && $user->getEmail() === 'super.admin@orange.fr') {
            return true;
        }

        // you know $subject is a Task object, thanks to `supports()`
        /** @var Task $Task */
        $task = $subject;

        if ($this->security->isGranted('ROLE_ADMIN') && $task->getUser() === null) {
            return true;
        }

        return match($attribute) {
            self::TOGGLE => $this->canToggle($task, $user),
            self::DELETE => $this->canDelete($task, $user),
            default => throw new \LogicException('Ce voteur ne devrait pas être atteint.')
        };
    }

    private function canToggle(Task $task, User $user): bool
    {
        return $user === $task->getUser();
    }

    private function canDelete(Task $task, User $user): bool
    {
        return $user === $task->getUser();
    }
}
