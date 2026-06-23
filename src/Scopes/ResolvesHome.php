<?php

namespace JeffersonGoncalves\LaravelZero\JsonConfig\Scopes;

/**
 * Self-contained home-directory + slug helpers shared by the scopes.
 *
 * Duplicated on purpose: this package declares no dependency on its sibling
 * packages, so it carries its own tiny home-dir resolver.
 */
trait ResolvesHome
{
    /**
     * Absolute path of the current user's home directory.
     *
     * Honors an explicit override, then HOME (POSIX), then
     * USERPROFILE / HOMEDRIVE+HOMEPATH (Windows), falling back to cwd.
     */
    protected function homeDir(?string $override = null): string
    {
        if (is_string($override) && $override !== '') {
            return rtrim($override, '/\\');
        }

        $home = getenv('HOME');

        if (! is_string($home) || $home === '') {
            $home = getenv('USERPROFILE');
        }

        if (! is_string($home) || $home === '') {
            $drive = getenv('HOMEDRIVE');
            $path = getenv('HOMEPATH');

            if (is_string($drive) && $drive !== '' && is_string($path) && $path !== '') {
                $home = $drive.$path;
            }
        }

        if (! is_string($home) || $home === '') {
            $home = getcwd() ?: '.';
        }

        return rtrim($home, '/\\');
    }

    /**
     * Normalize an arbitrary string into a safe lowercase filename slug.
     */
    protected function slugify(string $value): string
    {
        $value = strtolower($value);
        $value = preg_replace('#[^a-z0-9._-]+#', '-', $value) ?? $value;
        $value = trim($value, '-');

        return $value !== '' ? $value : 'default';
    }
}
