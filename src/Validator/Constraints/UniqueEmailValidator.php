<?php

namespace App\Validator\Constraints;

use App\Entity\Subscription;
use App\Repository\SubscriptionRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class UniqueEmailValidator
 * @package App\Validator\Constraints
 */
class UniqueEmailValidator extends ConstraintValidator
{
    /**
     * @var SubscriptionRepositoryInterface
     */
    protected $subscriptionRepository;

    /**
     * UniqueEmailValidator constructor.
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
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