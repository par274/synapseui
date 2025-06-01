<?php

namespace NativePlatform\Adapters\Ollama\Response\Chat;

use NativePlatform\Adapters\Ollama\Response\AbstractResponse;

class ChatResponse extends AbstractResponse
{
    public function __toString()
    {
        $messageContent = '';
        foreach ($this->getIterator() as $part)
        {
            if (isset($part['message']['content']))
            {
                $messageContent .= $part['message']['content'];
            }
        }

        return $messageContent;
    }
}
