<?php

namespace AcMarche\UrbaWeb\Tools;

use Symfony\Component\Dotenv\Dotenv;

class Env
{
    public static function loadEnv(): void
    {
        $dotenv = new Dotenv();
        $dir    = getcwd();
        try {
            $dotenv->load($dir.'/.env');
        } catch (\Exception $exception) {
            echo "error load env: ".$exception->getMessage();
        }
    }

    public static function getProjectDir(): string
    {
        $r = new \ReflectionObject(new self());

        if ( ! is_file($dir = $r->getFileName())) {
            throw new \LogicException(
                sprintf('Cannot auto-detect project dir for kernel of class "%s".', $r->name)
            );
        }

        $dir = $rootDir = \dirname($dir);
        while ( ! is_file($dir.'/composer.json')) {
            if ($dir === \dirname($dir)) {
                return $rootDir;
            }
            $dir = \dirname($dir);
        }

        return $dir;
    }
}
