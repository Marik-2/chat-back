<?php

namespace App;

final class Message
{
    private int $id;
    private int $ownerId;
    private string $text;
    private \DateTime $date;

    /**
     * Message constructor.
     * @param int $ownerId
     * @param string $text
     */
    public function __construct(int $ownerId, string $text)
    {
        $this->id = time() + rand(100, 999);
        $this->ownerId = $ownerId;
        $this->text = $text;
        $this->date =  new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'ownerId' => $this->ownerId,
            'text' => $this->text,
            'date' => $this->date->getTimestamp(),
        ];
    }
}
