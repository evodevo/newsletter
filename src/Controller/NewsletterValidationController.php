<?php

namespace App\Controller;

use App\Repository\SubscriptionRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NewsletterController
 * @package App\Controller
 */
class NewsletterValidationController extends AbstractController
{
    /**
     * @Route("/newsletter/validate-email", name="newsletter_validate_email")
     *
     * @param Request $request
     * @param SubscriptionRepositoryInterface $subscriptions
     *
     * @return Response
     */
    public function validateEmail(Request $request, SubscriptionRepositoryInterface $subscriptions): Response
    {
        $value = $request->query->get('value');

        $response = [
            'is_valid' => false,
        ];

        $subscription = $subscriptions->getByEmail($value);
        if (!$subscription) {
            $response['is_valid'] = true;
        } else {
            $response['message'] = 'This email is already subscribed';
        }

        return new JsonResponse($response);
    }
}