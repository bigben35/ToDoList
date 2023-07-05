<?php

namespace App\Security\Voter;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TaskVoter extends Voter
{
    public const OWN_TASK = 'OWN_TASK';
    public const ANONYMOUS_TASK = 'ANONYMOUS_TASK';
    public const TASK_EDIT = 'TASK_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::OWN_TASK, self::ANONYMOUS_TASK, self::TASK_EDIT])) {
            return false;
        }
        if (!$subject instanceof Task) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }
        // dd($attribute, $subject, $this->canEditAnonymousTask($subject, $user) || $this->canEditOwnTask($subject, $user));

        return match ($attribute) {
            self::OWN_TASK => $this->canEditOwnTask($subject, $user),
            self::ANONYMOUS_TASK => $this->canEditAnonymousTask($subject, $user),
            self::TASK_EDIT => $this->canEditAnonymousTask($subject, $user) || $this->canEditOwnTask($subject, $user),
            default => false,
        };
    }

    private function canEditOwnTask(Task $task, User $user): bool
    {
        return $task->getUser() === $user;
    }

    private function canEditAnonymousTask(Task $task, User $user): bool
    {
        // Check if the user is an admin and the task is anonymous
        if (in_array('ROLE_ADMIN', $user->getRoles()) && null === $task->getUser()) {
            return true;
        }

        return false;
    }
}
