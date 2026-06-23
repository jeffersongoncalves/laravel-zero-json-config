<?php

namespace JeffersonGoncalves\LaravelZero\JsonConfig;

/**
 * JSON-backed configuration store driven by a pluggable ConfigScope.
 *
 * The scope decides where the file lives (global, per-repo, per-project);
 * this service handles reading, writing and dot-notation access on top of it.
 *
 * Persistence: pretty-printed JSON, written with file mode 0600 and a parent
 * directory created with mode 0700 (best effort on platforms that honor it).
 *
 * Keys support dot-notation: "set('a.b', 1)" stores ['a' => ['b' => 1]].
 * A key containing a literal dot is therefore always treated as a path.
 */
final class JsonConfigService
{
    public function __construct(
        private readonly ConfigScope $scope,
    ) {}

    /**
     * Absolute path of the underlying JSON file (may not exist yet).
     */
    public function path(): string
    {
        return $this->scope->path();
    }

    /**
     * Whole config as an associative array (empty when the file is absent).
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $path = $this->path();

        if (! is_file($path)) {
            return [];
        }

        $raw = file_get_contents($path);

        if ($raw === false) {
            return [];
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Value at $key (dot-notation), or $default when missing.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $config = $this->all();

        if (array_key_exists($key, $config)) {
            return $config[$key];
        }

        $current = $config;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return $default;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * Whether $key (dot-notation) is present.
     */
    public function has(string $key): bool
    {
        $sentinel = "\0__missing__\0";

        return $this->get($key, $sentinel) !== $sentinel;
    }

    /**
     * Set $key (dot-notation) to $value and persist.
     */
    public function set(string $key, mixed $value): void
    {
        $config = $this->all();

        $segments = explode('.', $key);
        $ref = &$config;

        while (count($segments) > 1) {
            $segment = array_shift($segments);

            if (! isset($ref[$segment]) || ! is_array($ref[$segment])) {
                $ref[$segment] = [];
            }

            $ref = &$ref[$segment];
        }

        $ref[array_shift($segments)] = $value;
        unset($ref);

        $this->save($config);
    }

    /**
     * Remove $key (dot-notation) and persist. No-op when absent.
     */
    public function forget(string $key): void
    {
        $config = $this->all();

        $segments = explode('.', $key);
        $ref = &$config;

        while (count($segments) > 1) {
            $segment = array_shift($segments);

            if (! isset($ref[$segment]) || ! is_array($ref[$segment])) {
                return;
            }

            $ref = &$ref[$segment];
        }

        unset($ref[array_shift($segments)]);
        unset($ref);

        $this->save($config);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function save(array $config): void
    {
        $path = $this->path();
        $dir = dirname($path);

        if (! is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return;
        }

        file_put_contents($path, $json.PHP_EOL);
        @chmod($path, 0600);
    }
}
