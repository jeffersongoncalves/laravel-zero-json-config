<?php

use JeffersonGoncalves\LaravelZero\JsonConfig\Scopes\GlobalScope;
use JeffersonGoncalves\LaravelZero\JsonConfig\Scopes\PerProjectScope;
use JeffersonGoncalves\LaravelZero\JsonConfig\Scopes\PerRepoScope;

beforeEach(function () {
    $this->home = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lzjc-home';
    putenv('XDG_CONFIG_HOME');
});

afterEach(function () {
    putenv('XDG_CONFIG_HOME');
});

it('builds the global scope path under the home dir', function () {
    $scope = new GlobalScope('myapp', $this->home);

    $expected = $this->home.DIRECTORY_SEPARATOR.'.myapp'.DIRECTORY_SEPARATOR.'config.json';

    expect($scope->path())->toBe($expected);
});

it('builds the per-repo scope path under ~/.config when XDG is unset', function () {
    $scope = new PerRepoScope('myapp', 'owner/repo', $this->home);

    $expected = $this->home.DIRECTORY_SEPARATOR.'.config'
        .DIRECTORY_SEPARATOR.'myapp'
        .DIRECTORY_SEPARATOR.'owner-repo.json';

    expect($scope->path())->toBe($expected);
});

it('honors XDG_CONFIG_HOME for the per-repo scope', function () {
    $xdg = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lzjc-xdg';
    putenv('XDG_CONFIG_HOME='.$xdg);

    $scope = new PerRepoScope('myapp', 'my-slug', $this->home);

    $expected = $xdg.DIRECTORY_SEPARATOR.'myapp'.DIRECTORY_SEPARATOR.'my-slug.json';

    expect($scope->path())->toBe($expected);
});

it('builds the per-project scope path from base + default file name', function () {
    $base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lzjc-project';

    $scope = new PerProjectScope($base, appName: 'myapp');

    $expected = $base.DIRECTORY_SEPARATOR.'myapp.json';

    expect($scope->path())->toBe($expected);
});

it('builds the per-project scope path with an explicit file name', function () {
    $base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lzjc-project';

    $scope = new PerProjectScope($base, 'custom.json');

    $expected = $base.DIRECTORY_SEPARATOR.'custom.json';

    expect($scope->path())->toBe($expected);
});
