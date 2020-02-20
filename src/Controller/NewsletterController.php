<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Form\SubscriptionType;
use App\Repository\SubscriptionRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NewsletterController
 * @package App\Controller
 */
class NewsletterController extends AbstractController
{
    /**
     * @Route("/newsletter", name="newsletter")
     *
     * @param Request $request
     * @param SubscriptionRepositoryInterface $subscriptions
     *
     * @return Response
     */
    public function index(Request $request, SubscriptionRepositoryInterface $subscriptions): Response
    {
        $subscription = new Subscription();

        $form = $this->createForm(SubscriptionType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subscriptions->add($subscription);

            $this->addFlash('success', 'subscription.created_successfully');

            return $this->redirectToRoute('newsletter');
        }

        return $this->render('newsletter/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}