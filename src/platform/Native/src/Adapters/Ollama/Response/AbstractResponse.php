<?php

namespace NativePlatform\Adapters\Ollama\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

use ArrayObject;

abstract class AbstractResponse
{
    protected $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getIterator(): ArrayObject
    {
        $response = [];
        if ($this->response instanceof ResponseInterface)
        {
            while (!$this->response->getBody()->eof())
            {
                $line = $this->readClientLine($this->response->getBody());

                if (empty($line)) continue;

                $response[] = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
                if (isset($response['error']))
                {
                    throw new \Exception($response['error']);
                }
            }
        }

        return new ArrayObject($response);
    }

    public function toArray(): array
    {
        $iterator = iterator_to_array($this->getIterator());

        if (count($iterator) <= 1)
        {
            return $iterator[0];
        }
        else
        {
            return $iterator;
        }
    }

    private function readClientLine(StreamInterface $stream): string
    {
        $buffer = '';

        while (!$stream->eof())
        {
            if ('' === ($byte = $stream->read(1)))
            {
                return $buffer;
            }
            $buffer .= $byte;
            if ($byte === "\n")
            {
                break;
            }
        }

        return $buffer;
    }
}
