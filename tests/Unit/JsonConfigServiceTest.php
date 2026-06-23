<?php

use JeffersonGoncalves\LaravelZero\JsonConfig\JsonConfigService;
use JeffersonGoncalves\LaravelZero\JsonConfig\Scopes\PerProjectScope;

beforeEach(function () {
    $this->base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lzjc-'.uniqid('', true);
    @mkdir($this->base, 0700, true);
    $this->scope = new PerProjectScope($this->base, 'config.json');
    $this->service = new JsonConfigService($this->scope);
});

afterEach(function () {
    $path = $this->scope->path();
    if (is_file($path)) {
        @unlink($path);
    }
    @rmdir($this->base);
});

it('returns the default when a key is absent', function () {
    expect($this->service->get('missing', 'fallback'))->toBe('fallback');
    expect($this->service->get('missing'))->toBeNull();
});

it('returns an empty array when the file does not exist', function () {
    expect($this->service->all())->toBe([]);
    expect(is_file($this->scope->path()))->toBeFalse();
});

it('round-trips set -> get -> all', function () {
    $this->service->set('name', 'jeff');
    $this->service->set('count', 3);

    expect($this->service->get('name'))->toBe('jeff');
    expect($this->service->get('count'))->toBe(3);
    expect($this->service->all())->toBe(['name' => 'jeff', 'count' => 3]);
});

it('reports presence with has and removes with forget', function () {
    $this->service->set('token', 'abc');

    expect($this->service->has('token'))->toBeTrue();
    expect($this->service->has('nope'))->toBeFalse();

    $this->service->forget('token');

    expect($this->service->has('token'))->toBeFalse();
    expect($this->service->all())->toBe([]);
});

it('persists across instances', function () {
    $this->service->set('persisted', 'yes');

    $fresh = new JsonConfigService($this->scope);

    expect($fresh->get('persisted'))->toBe('yes');
    expect($fresh->all())->toBe(['persisted' => 'yes']);
});

it('writes the file to the scope path as pretty json', function () {
    $this->service->set('a', 1);

    $path = $this->scope->path();

    expect(is_file($path))->toBeTrue();
    expect($path)->toBe($this->base.DIRECTORY_SEPARATOR.'config.json');

    $raw = file_get_contents($path);
    expect($raw)->toContain("{\n    \"a\": 1\n}");
});

it('supports dot-notation for nested keys', function () {
    $this->service->set('auth.token', 'xyz');
    $this->service->set('auth.user', 'jeff');

    expect($this->service->get('auth.token'))->toBe('xyz');
    expect($this->service->get('auth'))->toBe(['token' => 'xyz', 'user' => 'jeff']);
    expect($this->service->has('auth.user'))->toBeTrue();

    $this->service->forget('auth.token');

    expect($this->service->has('auth.token'))->toBeFalse();
    expect($this->service->get('auth'))->toBe(['user' => 'jeff']);
});
