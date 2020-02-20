<?php

namespace App\Validator\Constraints;

use App\Entity\Subscription;
use App\Repository\SubscriptionRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueEmailValidator extends ConstraintValidator
{
    protected $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        $entity = $this->context->getObject();
        if (!$entity instanceof Subscription) {
            return;
        }

        $subscription = $this->subscriptionRepository->getByEmail($value);
        if ($subscription && $entity->getId() !== $subscription->getId()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}