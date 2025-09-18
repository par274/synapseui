<?php

namespace PlatformBridge;

/**
 * Holds configuration data for the application.  
 *
 * The class filters environment variables that start with one of the prefixes
 * defined in {@see $startWith}.  It also provides helper methods to retrieve
 * commonly‑used values (URLs, secrets, database credentials, etc.) and to
 * temporarily isolate a subset of those variables.
 */
class BridgeConfig
{
    /**
     * Prefixes that indicate which environment variables should be loaded.
     *
     * @var array<int,string>
     */
    private array $startWith = ['APP'];

    /**
     * The filtered environment variables that are currently active.
     *
     * @var array<string,mixed>
     */
    protected array $env = [];

    /**
     * Stores the original values before isolation so they can be restored.
     *
     * @var array<string,mixed>
     */
    protected array $isolated = [];

    /**
     * Creates a new configuration instance from an array of environment
     * variables.  Only variables whose keys start with one of the prefixes in
     * {@see $startWith} are kept.
     *
     * @param array<string,mixed> $env All available environment variables.
     */
    public function __construct(array $env)
    {
        foreach ($env as $key => $value)
        {
            foreach ($this->startWith as $start)
            {
                if (str_starts_with($key, $start . '_'))
                {
                    $this->env[$key] = $value;
                }
            }
        }
    }

    /**
     * Isolates a subset of the current environment variables.  
     *
     * Variables whose keys start with any of the prefixes supplied in
     * `$starts` are kept in {@see $env}.  All other variables are moved to
     * {@see $isolated} so that they can be restored later.
     *
     * @param array<string> $starts Prefixes used for isolation (e.g. ['DB']).
     */
    public function isolate(array $starts)
    {
        $env = $this->env;
        $this->env = [];

        foreach ($env as $key => $value)
        {
            foreach ($starts as $start)
            {
                if (str_starts_with($key, $start . '_'))
                {
                    $this->env[$key] = $value;
                }
            }

            $this->isolated[$key] = $value;
        }
    }

    /**
     * Restores the environment variables that were previously isolated.
     *
     * This is effectively the inverse of {@see isolate()} and clears the
     * internal isolation buffer afterward.
     */
    public function endIsolate(): void
    {
        self::__construct($this->isolated);
        $this->isolated = [];
    }

    /**
     * Returns all currently active environment variables.
     *
     * @return array<string,mixed> The filtered key/value pairs.
     */
    public function all(): array
    {
        return $this->env;
    }

    /**
     * Retrieves a value from the configuration by key.
     *
     * If the key does not exist, the provided default is returned instead.
     *
     * @param string $key     The environment variable name to look up.
     * @param mixed  $default Value to return if `$key` is missing. Defaults
     *                        to `null`.
     *
     * @return mixed The value of the configuration key or the default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->env[$key] ?? $default;
    }

    /**
     * Sets a configuration value for the given key.
     *
     * @param string $key   The environment variable name to set.
     * @param string $value The new value.
     */
    public function set(string $key, string $value): void
    {
        $this->env[$key] = $value;
    }

    /**
     * Returns the application secret key.
     *
     * If not defined in the environment, a fallback of `'default-secret'` is
     * used.  This value should normally be stored securely and never exposed.
     *
     * @return string The secret key.
     */
    public function getSecret(): string
    {
        return $this->get('APP_SECRET', 'default-secret');
    }

    /**
     * Returns the base URL for the application.
     *
     * Trailing slashes are removed to keep URLs consistent.  If no value is
     * defined, `http://localhost` is used.
     *
     * @return string The sanitized application URL.
     */
    public function getAppUrl(): string
    {
        return rtrim($this->get('APP_URL', 'http://localhost'), '/');
    }

    /**
     * Returns the current environment (e.g. `dev`, `prod`).
     *
     * Defaults to `'prod'` if not set.
     *
     * @return string The application environment name.
     */
    public function getEnv(): string
    {
        return $this->get('APP_ENV', 'prod');
    }

    /**
     * Determines which LLM adapter method should be used (`client` or `server`).
     *
     * If the value is not one of those two strings, it defaults to `'client'`.
     *
     * @return string Either `'client'` or `'server'`.
     */
    public function getLLMAdapterMethod(): string
    {
        if (
            $this->get('APP_LLM_ADAPTER_METHOD') !== 'client'
            && $this->get('APP_LLM_ADAPTER_METHOD') !== 'server'
        )
        {
            $this->env['APP_LLM_ADAPTER_METHOD'] = 'client';
        }

        return $this->get('APP_LLM_ADAPTER_METHOD', 'client');
    }

    /**
     * Returns the name of the LLM adapter to use.
     *
     * @return string The adapter identifier, e.g. `'ollama'`.
     */
    public function getLLMAdapter(): string
    {
        return $this->get('APP_LLM_ADAPTER', 'ollama');
    }

    /**
     * Returns the base URL for the LLM adapter service.
     *
     * @return string The adapter endpoint URL.
     */
    public function getLLMAdapterUrl(): string
    {
        return $this->get('APP_LLM_ADAPTER_URL', 'http://localhost:11434');
    }

    /**
     * Returns the API key for the LLM adapter, if one is configured.
     *
     * @return string|null The API key or `null` when not set.
     */
    public function getLLMAdapterApiKey(): string|null
    {
        return $this->get('APP_LLM_ADAPTER_API_KEY', null);
    }

    /**
     * Returns the selection utilization.
     *
     * @return string
     */
    public function getLLMUtilization(): string
    {
        return $this->get('APP_LLM_UTILIZATION', 'cpu');
    }

    /**
     * Aggregates the database connection parameters into a single array.
     *
     * @return array<string,mixed> Keys include `driver`, `host`, `port`,
     *                            `dbname`, `user` and `password`.
     */
    public function getDatabaseParams(): array
    {
        return [
            'driver'   => $this->get('APP_DB_DRIVER'),
            'host'     => $this->get('APP_DB_HOST'),
            'port'     => $this->get('APP_DB_PORT'),
            'dbname'   => $this->get('APP_DB_NAME'),
            'user'     => $this->get('APP_DB_USER'),
            'password' => $this->get('APP_DB_PASS'),
        ];
    }

    /**
     * Builds a URL path for an asset relative to the configured assets base.
     *
     * @param string $path Path to the asset, e.g. `'css/style.css'`.
     *
     * @return string The fully qualified asset URL (e.g. `/assets/css/style.css`).
     */
    public function asset(string $path): string
    {
        $base = '/' . trim($this->get('APP_ASSET_PATH') ?? 'assets', '/');
        return $base . '/' . ltrim($path, '/');
    }
}
