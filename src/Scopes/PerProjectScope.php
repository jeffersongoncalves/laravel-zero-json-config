<?php

namespace JeffersonGoncalves\LaravelZero\JsonConfig\Scopes;

use JeffersonGoncalves\LaravelZero\JsonConfig\ConfigScope;

/**
 * Per-project config living inside the project directory itself:
 *
 *     <basePath>/<fileName>
 *
 * Defaults to "<basePath>/<app>.json", e.g. the file is committed next to
 * the project it configures (like screentest.json).
 */
final class PerProjectScope implements ConfigScope
{
    private readonly string $fileName;

    public function __construct(
        private readonly string $basePath,
        ?string $fileName = null,
        string $appName = 'config',
    ) {
        $this->fileName = $fileName ?? $appName.'.json';
    }

    public function path(): string
    {
        return rtrim($this->basePath, '/\\').DIRECTORY_SEPARATOR.$this->fileName;
    }
}
