<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\UniqueEmail;

/**
 * Class Subscription
 * @package App\Entity
 */
class Subscription
{
    /**
     * @var int
     */
    private $id;

    /**
     * @Assert\NotBlank(
     *     groups={"Edit"},
     *     message = "Name is required."
     * )
     * @Assert\Length(
     *     groups={"Edit"},
     *     max=50,
     *     maxMessage = "Name cannot be longer than {{ limit }} characters"
     * )
     */
    private $name;

    /**
     * @Assert\NotBlank(
     *     groups={"Edit"},
     *     message = "Email is required."
     * )
     * @Assert\Email(
     *     groups={"Edit"},
     *     message = "Email is invalid."
     * )
     * @Assert\Length(
     *     groups={"Edit"},
     *     max=254,
     *     maxMessage = "Email cannot be longer than {{ limit }} characters"
     * )
     * @UniqueEmail(
     *     groups={"Edit"},
     *     message = "Another subscription already exists with this email."
     * )
     */
    private $email;

    /**
     * @Assert\Choice(
     *     min=1,
     *     multiple=true,
     *     callback="getAvailableCategories",
     *     minMessage="You must select at least {{ limit }} category",
     *     message="The category you selected is not valid"
     * )
     */
    private $categories = [];

    private $createdAt;

    /**
     * @var array
     */
    private static $availableCategories = [
        'Sports' => 'sports',
        'Technology' => 'technology',
        'Politics' => 'politics',
        'Weather' => 'weather',
        'Business' => 'business',
    ];

    /**
     * Subscription constructor.
     */
    public function __construct()
    {
        $this->createdAt = time();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return array
     */
    public static function getAvailableCategories(): array
    {
        return self::$availableCategories;
    }
}