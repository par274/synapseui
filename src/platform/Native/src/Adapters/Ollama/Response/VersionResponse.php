<?php

namespace NativePlatform\Adapters\Ollama\Response;

use NativePlatform\Adapters\Ollama\Response\AbstractResponse;

class VersionResponse extends AbstractResponse
{
    public function getOllamaVersion(): string
    {
        return $this->getIterator()->offsetGet(0)['version'];
    }

    public function __toString(): string
    {
        return $this->getIterator()->offsetGet(0)['version'];
    }
}
