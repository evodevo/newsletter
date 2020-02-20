<?php

namespace App\Repository;

use App\Entity\Subscription;
use Jajo\JSONDB;

/**
 * Class SubscriptionFileRepository
 * @package App\Repository
 */
class SubscriptionFileRepository implements SubscriptionRepositoryInterface
{
    const DEFAULT_SUBSCRIPTIONS_FILE = 'subscriptions.json';

    /**
     * @var JSONDB
     */
    private $jsonFile;

    /**
     * @var string
     */
    private $fileName;

    /**
     * SubscriptionFileRepository constructor.
     * @param JSONDB $jsonFile
     * @param string $fileName
     */
    public function __construct(JSONDB $jsonFile, $fileName = self::DEFAULT_SUBSCRIPTIONS_FILE)
    {
        $this->jsonFile = $jsonFile;
        $this->fileName  = $fileName;
    }

    /**
     * @param string $sort
     * @param string $order
     * @return array
     */
    public function getAll($sort = 'createdAt', $order = 'asc')
    {
        $order = ($order === 'asc' ? JSONDB::ASC : JSONDB::DESC);

        $rows = $this->jsonFile->select('id, name, email, categories, createdAt')
            ->from($this->fileName)
            ->order_by($sort, $order)
            ->get();

        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->createEntity($row);
        }

        return $collection;
    }

    /**
     * @param $id
     *
     * @return Subscription|null
     */
    public function getById($id)
    {
        $data = $this->jsonFile->select('id, name, email, categories, createdAt')
            ->from($this->fileName)
            ->where( [ 'id' => $id ] )
            ->get();

        if (!$data) {
            return null;
        }

        return $this->createEntity(current($data));
    }

    /**
     * @param $email
     * @return Subscription|null
     */
    public function getByEmail($email)
    {
        $data = $this->jsonFile->select('id, name, email, categories, createdAt')
            ->from($this->fileName)
            ->where( [ 'email' => $email ] )
            ->get();

        if (!$data) {
            return null;
        }

        return $this->createEntity(current($data));
    }

    /**
     * @param Subscription $subscription
     */
    public function add(Subscription $subscription)
    {
        $data = [
            'name' => $subscription->getName(),
            'email' => $subscription->getEmail(),
            'categories' => $subscription->getCategories(),
            'createdAt' => $subscription->getCreatedAt(),
        ];

        if ($subscription->getId()) {
            $this->jsonFile->update($data)
                ->from( $this->fileName )
                ->where(['id' => $subscription->getId()])
                ->trigger();
        } else {
            $id = count($this->jsonFile->from($this->fileName)->content) + 1;
            $data['id'] = $id;
            $id = $this->jsonFile->insert($this->fileName, $data);

            $subscription->setId($id);
        }
    }

    /**
     * @param Subscription $subscription
     */
    public function remove(Subscription $subscription)
    {
        $this->jsonFile->delete()
            ->from($this->fileName)
            ->where( [ 'id' => $subscription->getId() ] )
            ->trigger();
    }

    protected function createEntity(array $data)
    {
        $entity = new Subscription();
        $entity->setId(isset($data['id']) ? $data['id'] : null);
        $entity->setName(isset($data['name']) ? $data['name'] : null);
        $entity->setEmail(isset($data['email']) ? $data['email'] : null);
        $entity->setCategories(isset($data['categories']) ? $data['categories'] : null);
        $entity->setCreatedAt(isset($data['createdAt']) ? $data['createdAt'] : null);

        return $entity;
    }
}