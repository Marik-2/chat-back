<?php

namespace App;

use PHPSocketIO\Socket;
use Workerman\Worker;
use PHPSocketIO\SocketIO;

final class Server
{
    private const SERVER_PORT = 2021;

    private const EVENT_CONNECTION = 'connection';
    private const EVENT_UPDATE_ROOM_LIST = 'update_room_list';
    private const EVENT_UPDATE_MESSAGE_LIST = 'update_message_list';

    private SocketIO $io;

    /** @var Room[] */
    private array $rooms = [];

    /**
     * Server constructor.
     */
    public function __construct()
    {
        $this->CORS();

        $this->io = new SocketIO(self::SERVER_PORT);

        //=============
        $room = new Room('Test Room');
        $this->rooms = [
            $room->getId() => $room,
        ];
        //=============

        $this->io->on(self::EVENT_CONNECTION, fn(Socket $socket) => new Connection($this, $socket));
    }

    /**
     * @param Room $room
     */
    public function sendMessageList(Room $room): void
    {
        $this->io->to($room->getId())->emit(self::EVENT_UPDATE_MESSAGE_LIST, $this->getMessageList($room));
    }

    /**
     * @param Room $room
     * @return array
     */
    public function getMessageList(Room $room): array
    {
        return array_map(fn (Message $message) => $message->toArray(), $room->getMessages());
    }

    /**
     *
     */
    public function sendRoomList(): void
    {
        $this->io->emit(self::EVENT_UPDATE_ROOM_LIST, $this->getRoomList());
    }

    /**
     * @return array
     */
    public function getRoomList(): array
    {
        $rooms = array_map(fn(Room $room) => $room->toArray(), $this->rooms);
        return array_values($rooms);
    }

    /**
     * @param int $roomId
     * @return Room|null
     */
    public function getRoomById(int $roomId): ?Room
    {
        return $this->rooms[$roomId] ?? null;
    }

    /**
     *
     */
    public function start(): void
    {
        Worker::runAll();
    }

    /**
     * Убирает ограничения междоменных запросов
     */
    private function CORS(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: X-Requested-With");
    }
}
