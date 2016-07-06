<?php

namespace Equip\Queue\Serializer;

use Equip\Queue\Message;

interface MessageSerializerInterface
{
    /**
     * Serializes the message object
     *
     * @param Message $message
     *
     * @return string
     */
    public function serialize(Message $message);

    /**
     * Deserialize the message object
     *
     * @param string $data
     *
     * @return Message
     */
    public function deserialize($data);
}
