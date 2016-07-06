<?php

namespace Equip\Queue\Serializer;

use Equip\Queue\Message;

class JsonSerializer implements MessageSerializerInterface
{
    /**
     * @inheritdoc
     */
    public function serialize(Message $message)
    {
        return json_encode([
            'queue' => $message->queue(),
            'handler' => $message->handler(),
            'data' => $message->data(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function deserialize($data)
    {
        $message = json_decode($data, true);

        return new Message(
            $message['queue'],
            $message['handler'],
            $message['data']
        );
    }
}
