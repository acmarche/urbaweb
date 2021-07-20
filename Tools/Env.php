<?php

namespace AcMarche\UrbaWeb\Tools;

use Symfony\Component\Dotenv\Dotenv;

class Env
{
    public static function loadEnv(): void
    {
        $dotenv = new Dotenv();
        try {
            $dotenv->load(__DIR__.'/.env');
        } catch (\Exception $exception) {
            echo "error load env: ".$exception->getMessage();
        }
    }
}
