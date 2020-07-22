<?php

namespace App;

use Exception;
use PHPSocketIO\Socket;

final class Connection
{
    private const EVENT_JOIN = 'join';
    private const EVENT_DISCONNECTION = 'disconnect';
    private const EVENT_SET_NAME = 'set_name';
    private const EVENT_LOGIN = 'login';
    private const EVENT_NEW_MESSAGE = 'new_message';

    private Server $server;
    private Socket $socket;

    private User $user;
    private Room $room;

    /**
     * Connection constructor.
     * @param Server $server
     * @param Socket $socket
     */
    public function __construct(Server $server, Socket $socket)
    {
        $this->server = $server;
        $this->socket = $socket;

        $this->run();
    }

    /**
     *
     */
    private function run()
    {
        $this->server->sendRoomList();
        $this->initHandlers();
    }

    /**
     * @throws Exception
     */
    private function sendCurrentUser()
    {
        $user = $this->getCurrentUser();

        if (null === $user) {
            throw new Exception('Нет текущего пользователя');
        }

        $this->socket->emit(self::EVENT_LOGIN, $user->toArray());
    }

    /**
     *
     */
    private function initHandlers(): void
    {
        $this->socket->on(self::EVENT_JOIN, fn(int $roomId) => $this->joinHandler($roomId));
        $this->socket->on(self::EVENT_DISCONNECTION, fn() => $this->disconnectionHandler());
        $this->socket->on(self::EVENT_SET_NAME, fn(string $name) => $this->setNameHandler($name));
        $this->socket->on(self::EVENT_NEW_MESSAGE, fn(string $message) => $this->messageHandler($message));
    }

    /**
     * @param int $roomId
     * @throws Exception
     */
    private function joinHandler(int $roomId): void
    {
        $room = $this->server->getRoomById($roomId);

        if (null === $room) {
            throw new Exception('Такой комнаты не существует');
        }

        $user = $this->getCurrentUser();

        if (null === $user) {
            throw new Exception('Нет авторизованного пользователя');
        }

        $room->addUser($user);
        $this->setCurrentRoom($room);
        $this->socket->join($room->getId());
        $this->server->sendMessageList($room);

        $this->server->sendRoomList();
    }

    /**
     *
     */
    private function disconnectionHandler(): void
    {
        $room = $this->getCurrentRoom();
        $user = $this->getCurrentUser();
        $room->disconnectUser($user->getId());
        $this->server->sendRoomList();
    }

    /**
     * @param string $name
     * @throws Exception
     */
    private function setNameHandler(string $name): void
    {
        $user = new User($name);
        $this->setCurrentUser($user);
        $this->sendCurrentUser();
    }

    /**
     * @param string $message
     */
    private function messageHandler(string $message): void
    {
        $user = $this->getCurrentUser();
        $room = $this->getCurrentRoom();
        $message = new Message($user->getId(), $message);
        $room->addMessage($message);
        $this->server->sendMessageList($room);
    }

    /**
     * Пользователь текущего соеденения
     * @return User|null
     */
    private function getCurrentUser(): ?User
    {
        // TODO: Кидать исключение
        return $this->user ?? null;
    }

    /**
     * @param User $user
     * @return void
     */
    private function setCurrentUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Комната, в которой находится пользователь
     * @return User|null
     */
    private function getCurrentRoom(): ?Room
    {
        // TODO: Кидать исключение
        return $this->room ?? null;
    }

    /**
     * @param Room $room
     * @return void
     */
    private function setCurrentRoom(Room $room): void
    {
        $this->room = $room;
    }
}
