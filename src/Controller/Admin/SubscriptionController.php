<?php

namespace App\Controller\Admin;

use App\Form\SubscriptionType;
use App\Repository\SubscriptionRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("has_role('ROLE_ADMIN')")
 */
class SubscriptionController extends AbstractController
{
    /**
     * @Route("/admin/subscriptions", name="admin_subscription_index")
     *
     * @param Request $request
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     *
     * @return Response
     */
    public function indexAction(Request $request, SubscriptionRepositoryInterface $subscriptionRepository): Response
    {
        $sort = $request->query->get('sort', 'createdAt');
        $order = $request->query->get('order', 'desc');

        return $this->render('admin/newsletter/index.html.twig', [
            'subscriptions' => $subscriptionRepository->getAll($sort, $order),
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    /**
     * @Route(
     *     "/admin/subscriptions/{id}/edit",
     *     name="admin_subscription_edit",
     *     methods={"GET", "PATCH"},
     *     requirements={"id"="[0-9a-f-]+"}
     * )
     *
     * @param Request $request
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function edit(Request $request, SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $id = $request->attributes->get('id');

        $subscription = $subscriptionRepository->getById($id);
        if (!$subscription) {
            throw new NotFoundHttpException('Subscription not found with id ' . $id);
        }

        $form = $this->createForm(SubscriptionType::class, $subscription, [
            'validation_groups' => 'Edit',
            'method' => 'PATCH',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subscriptionRepository->add($subscription);

            $this->addFlash('success', 'subscription.updated_successfully');

            return $this->redirectToRoute('admin_subscription_index');
        }

        return $this->render('admin/newsletter/edit.html.twig', [
            'subscription' => $subscription,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     "/admin/subscriptions/{id}/delete",
     *     name="admin_subscription_delete",
     *     methods={"POST"},
     *     requirements={"id"="[0-9a-f-]+"}
     * )
     *
     * @param Request $request
     * @param SubscriptionRepositoryInterface $subscriptionRepository
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function delete(Request $request, SubscriptionRepositoryInterface $subscriptionRepository)
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('admin_subscription_index');
        }

        $id = $request->attributes->get('id');

        $subscription = $subscriptionRepository->getById($id);
        if (!$subscription) {
            throw new NotFoundHttpException('Subscription not found with id ' . $id);
        }

        $subscriptionRepository->remove($subscription);

        $this->addFlash('success', 'subscription.deleted_successfully');

        return $this->redirectToRoute('admin_subscription_index');
    }
}