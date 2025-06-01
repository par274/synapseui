<?php

namespace NativePlatform\Adapters\Ollama\Response\Completion;

use NativePlatform\Adapters\Ollama\Response\AbstractResponse;

class CompletionResponse extends AbstractResponse
{
    public function __toString()
    {
        $messageContent = '';
        foreach ($this->getIterator() as $part)
        {
            if (isset($part['response']))
            {
                $messageContent .= $part['response'];
            }
        }

        return $messageContent;
    }
}
