<div class="filament-hidden">

![laravel-zero-json-config](https://raw.githubusercontent.com/jeffersongoncalves/laravel-zero-json-config/main/art/jeffersongoncalves-laravel-zero-json-config.png)

</div>

# laravel-zero-json-config

A tiny, self-contained JSON configuration service for PHP CLI tools (built with
Laravel Zero in mind, but framework-free). It separates **where** a config file
lives (the *scope*) from **how** values are read and written (the *service*),
so you can swap a global config for a per-repo or per-project one without
touching call sites.

- No runtime dependencies (only `php: ^8.2`).
- Three ready-made scope strategies.
- `get` / `set` / `all` / `has` / `forget` with **dot-notation** support.
- Safe writes: pretty JSON, file mode `0600`, parent dir `0700`.

## Installation

```bash
composer require jeffersongoncalves/laravel-zero-json-config
```

## Scopes

A scope implements `ConfigScope` and answers one question: `path(): string`,
the absolute path of the JSON file.

| Scope | Path | Use case |
|-------|------|----------|
| `GlobalScope` | `~/.<app>/config.json` | One machine-wide config per app |
| `PerRepoScope` | `${XDG_CONFIG_HOME:-~/.config}/<app>/<slug>.json` | One config per repository |
| `PerProjectScope` | `<basePath>/<fileName>` (default `<basePath>/<app>.json`) | Config committed next to the project |

```php
use JeffersonGoncalves\LaravelZero\JsonConfig\JsonConfigService;
use JeffersonGoncalves\LaravelZero\JsonConfig\Scopes\GlobalScope;
use JeffersonGoncalves\LaravelZero\JsonConfig\Scopes\PerRepoScope;
use JeffersonGoncalves\LaravelZero\JsonConfig\Scopes\PerProjectScope;

// Global: ~/.myapp/config.json
$config = new JsonConfigService(new GlobalScope('myapp'));

// Per-repo: ~/.config/myapp/owner-repo.json (slug is caller-supplied)
$config = new JsonConfigService(new PerRepoScope('myapp', 'owner-repo'));

// Per-project: ./myapp.json (next to where you run the tool)
$config = new JsonConfigService(new PerProjectScope(getcwd(), appName: 'myapp'));
```

The `homeDir` constructor argument on `GlobalScope` / `PerRepoScope` lets you
override the home directory (useful for tests). `PerRepoScope` also honors the
`XDG_CONFIG_HOME` environment variable.

## Usage

```php
$config->set('token', 'secret');
$config->get('token');             // 'secret'
$config->get('missing', 'fallback'); // 'fallback'

$config->has('token');             // true
$config->all();                    // ['token' => 'secret']
$config->forget('token');

$config->path();                   // absolute path of the JSON file
```

### Dot-notation

Keys containing a dot are treated as nested paths:

```php
$config->set('auth.token', 'xyz');
$config->get('auth');        // ['token' => 'xyz']
$config->get('auth.token');  // 'xyz'
$config->forget('auth.token');
```

When `get` is called, a flat top-level key matching the literal string is
returned first if it exists; otherwise the key is split on `.` and resolved
through the nested array.

## Public API

- `JeffersonGoncalves\LaravelZero\JsonConfig\ConfigScope` (interface) — `path(): string`
- `JeffersonGoncalves\LaravelZero\JsonConfig\JsonConfigService` — `get`, `set`, `all`, `has`, `forget`, `path`
- `JeffersonGoncalves\LaravelZero\JsonConfig\Scopes\GlobalScope`
- `JeffersonGoncalves\LaravelZero\JsonConfig\Scopes\PerRepoScope`
- `JeffersonGoncalves\LaravelZero\JsonConfig\Scopes\PerProjectScope`

## License

MIT
