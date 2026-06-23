<?php

namespace JeffersonGoncalves\LaravelZero\JsonConfig;

/**
 * Resolves the absolute filesystem path of a JSON config file.
 *
 * Implementations encode a storage strategy (global, per-repo, per-project).
 * The path may point to a file that does not exist yet; JsonConfigService
 * is responsible for creating the parent directory and the file on write.
 */
interface ConfigScope
{
    /**
     * Absolute path of the JSON config file for this scope.
     */
    public function path(): string;
}
