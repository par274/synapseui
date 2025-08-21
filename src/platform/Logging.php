<?php

declare(strict_types=1);

namespace PlatformBridge;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Very small logger that writes to a file.
 *
 *  - Extends AbstractLogger so you only need to implement log()
 *  - No external dependencies → great for demos or tiny projects
 */
final class Logging extends AbstractLogger
{
    private readonly string $file;

    public function __construct(string $logFile)
    {
        $this->file = $logFile;
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed  $level   One of the Psr\Log\LogLevel constants.
     * @param string $message The log message (may contain placeholders).
     * @param array  $context Context array for placeholders.
     */
    public function log($level, $message, array $context = []): void
    {
        // Basic interpolation – replace {foo} with $context['foo']
        foreach ($context as $key => $value)
        {
            if (is_scalar($value))
            {
                $message = str_replace('{' . $key . '}', (string)$value, $message);
            }
        }

        $record = sprintf(
            "[%s] %s: %s\n",
            strtoupper((string)$level),
            date('Y-m-d H:i:s'),
            $message
        );

        if (!file_exists($this->file))
        {
            $file = fopen($this->file, "wb");
            fwrite($file, "Logging: " . date('Y-m-d H:i:s') . "\n");
            fclose($file);
        }

        file_put_contents($this->file, $record, FILE_APPEND | LOCK_EX);
    }
}
