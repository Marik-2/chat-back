<?php

namespace App;

use Exception;

final class Room
{
    /** @var int */
    private const MAX_USERS = 2;

    private int $id;
    private string $name;

    /** @var User[] */
    private array $users = [];
    /** @var Message[] */
    private array $messages = [];

    /**
     * Room constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->id = time() + rand(100, 999);
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param User $user
     * @return $this
     * @throws Exception
     */
    public function addUser(User $user): self
    {
        if ($this->getCountUsers() < self::MAX_USERS) {
            $this->users[$user->getId()] = $user;
        } else {
            throw new Exception('Maximum users in this room');
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getCountUsers(): int
    {
        return count($this->getUsers());
    }

    /**
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param Message $message
     * @return $this
     */
    public function addMessage(Message $message): self
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'users' => $this->getUsers(),
            'countUsers' => $this->getCountUsers(),
            'maxUsers' => self::MAX_USERS,
        ];
    }

    /**
     * @param int $userId
     */
    public function disconnectUser(int $userId): void
    {
        unset($this->users[$userId]);
    }

    /**
     * @param int $userId
     * @return User|null
     */
    public function getUserById(int $userId): ?User
    {
        return $this->users[$userId] ?? null;
    }
}
