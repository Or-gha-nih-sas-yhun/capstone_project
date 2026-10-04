<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        putenv('APP_URL=http://localhost');
        $_ENV['APP_URL'] = 'http://localhost';
        $_SERVER['APP_URL'] = 'http://localhost';

        putenv('APP_KEY=base64:7vF65Dk9oU0v16M2oPjXn6Y7d8E9f0G1h2I3j4K5l6M=');
        $_ENV['APP_KEY'] = 'base64:7vF65Dk9oU0v16M2oPjXn6Y7d8E9f0G1h2I3j4K5l6M=';
        $_SERVER['APP_KEY'] = 'base64:7vF65Dk9oU0v16M2oPjXn6Y7d8E9f0G1h2I3j4K5l6M=';

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
