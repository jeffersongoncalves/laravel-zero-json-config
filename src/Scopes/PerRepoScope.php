<?php

namespace JeffersonGoncalves\LaravelZero\JsonConfig\Scopes;

use JeffersonGoncalves\LaravelZero\JsonConfig\ConfigScope;

/**
 * Per-repository config, XDG aware:
 *
 *     ${XDG_CONFIG_HOME:-~/.config}/<app>/<slug>.json
 *
 * One file per repository (identified by a caller-supplied slug), grouped
 * under a per-application directory.
 */
final class PerRepoScope implements ConfigScope
{
    use ResolvesHome;

    public function __construct(
        private readonly string $appName,
        private readonly string $slug,
        private readonly ?string $homeDir = null,
    ) {}

    public function path(): string
    {
        $xdg = getenv('XDG_CONFIG_HOME');

        if (is_string($xdg) && $xdg !== '') {
            $base = rtrim($xdg, '/\\');
        } else {
            $base = $this->homeDir($this->homeDir).DIRECTORY_SEPARATOR.'.config';
        }

        return $base
            .DIRECTORY_SEPARATOR.$this->slugify($this->appName)
            .DIRECTORY_SEPARATOR.$this->slugify($this->slug).'.json';
    }
}
