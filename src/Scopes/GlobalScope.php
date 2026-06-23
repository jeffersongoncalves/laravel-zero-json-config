<?php

namespace JeffersonGoncalves\LaravelZero\JsonConfig\Scopes;

use JeffersonGoncalves\LaravelZero\JsonConfig\ConfigScope;

/**
 * Global, machine-wide config stored in the user's home directory:
 *
 *     ~/.<app>/config.json
 *
 * One file per application, shared across every repo and project.
 */
final class GlobalScope implements ConfigScope
{
    use ResolvesHome;

    public function __construct(
        private readonly string $appName,
        private readonly ?string $homeDir = null,
    ) {}

    public function path(): string
    {
        return $this->homeDir($this->homeDir)
            .DIRECTORY_SEPARATOR.'.'.$this->slugify($this->appName)
            .DIRECTORY_SEPARATOR.'config.json';
    }
}
