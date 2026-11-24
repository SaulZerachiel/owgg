<?php

use Illuminate\Support\Env;

test('unpooled postgres URL is parsed into the direct connection config', function () {
    $repository = Env::getRepository();
    $original = $repository->get('DATABASE_URL_UNPOOLED');

    $repository->clear('DATABASE_URL_UNPOOLED');
    $repository->set(
        'DATABASE_URL_UNPOOLED',
        'postgresql://test:test@db-direct.example:5432/testdb?sslmode=require'
    );

    try {
        $config = require config_path('database.php');
    } finally {
        $repository->clear('DATABASE_URL_UNPOOLED');

        if ($original !== null) {
            $repository->set('DATABASE_URL_UNPOOLED', $original);
        }
    }

    expect($config['connections']['pgsql']['direct']['host'])->toBe('db-direct.example');
});
