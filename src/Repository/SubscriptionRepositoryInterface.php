<?php

namespace App\Repository;

use App\Entity\Subscription;

/**
 * Interface SubscriptionRepositoryInterface
 * @package App\Repository
 */
interface SubscriptionRepositoryInterface
{
    /**
     * @param $sort
     * @param $order
     * @return array
     */
    public function getAll($sort, $order);

    /**
     * @param $id
     * @return Subscription|null
     */
    public function getById($id);

    /**
     * @param $email
     * @return Subscription|null
     */
    public function getByEmail($email);

    /**
     * @param Subscription $subscription
     * @return void
     */
    public function add(Subscription $subscription);

    /**
     * @param Subscription $subscription
     * @return void
     */
    public function remove(Subscription $subscription);
}