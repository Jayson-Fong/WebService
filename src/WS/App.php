<?php

namespace WS;

use ArrayObject;
use Closure;
use Symfony\Component\Console\Application;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use WS\Mvc\Request;
use WS\Mvc\Response;
use WS\Mvc\Router;
use WS\Persistence\Persistence;
use WS\Repository\Repository;
use WS\TemplateExtension\Core;
use WS\Util\Database;
use WS\Util\Php;

/**
 * @author Jayson Fong <contact@jaysonfong.org>
 * @copyright Jayson Fong 2022
 */
class App
{

    private static ?App $instance = null;

    protected ArrayObject $objectCache;
    protected array $configuration;

    private function __construct(string $env) {
        $this->objectCache = new ArrayObject();
        $this->configuration = $this->getConfig($env);
    }

    public static function getInstance(string $env = 'prod'): App
    {
        if (is_null(self::$instance))
        {
            self::$instance = new App($env);
        }

        return self::$instance;
    }

    /**
     * @return Request
     */
    public function request(): Request
    {
        return $this->cachedClassRetrieval('request');
    }

    /**
     * @return Router
     */
    public function router(): Router
    {
        return $this->cachedClassRetrieval('router');
    }

    public function environment(): Environment
    {
        return $this->cachedClassRetrieval('environment', function () {
            $loader = new FilesystemLoader($this->configuration['template']['directory']);
            $envOptions = [];
            if (Php::getElementOrDefault($this->configuration['template'], 'useCache', false))
            {
                $envOptions['cache'] = Php::getElementOrDefault($this->configuration['template'], 'cacheDirectory', 'templates/cache');
            }

            $environment = new Environment($loader, $envOptions);
            $environment->addExtension(new Core());

            foreach (scandir('src/WS/TemplateExtension') as $outerFile) {
                if (in_array($outerFile, ['.', '..']))
                    continue;

                if (is_dir("src/WS/TemplateExtension/$outerFile"))
                {
                    foreach (scandir("src/WS/TemplateExtension/$outerFile") as $innerFile)
                    {
                        if (in_array($innerFile, ['.', '..']))
                            continue;

                        if (!is_file("src/WS/TemplateExtension/$outerFile/$innerFile"))
                            continue;

                        $innerFile = basename($innerFile, '.php');

                        $className = "WS/TemplateExtension/$outerFile/$innerFile";
                        if (class_exists($className))
                        {
                            $environment->addExtension(new ($className)());
                        }
                    }
                }
                else if (is_file("src/WS/TemplateExtension/$outerFile"))
                {
                    $outerFile = basename($outerFile, '.php');
                    $className = "WS/TemplateExtension/$outerFile";
                    if (class_exists($className))
                    {
                        $environment->addExtension(new ($className)());
                    }
                }
            }

            return $environment;
        });
    }

    public function getConfigurationOption(string... $indexes): mixed
    {
        $configuration = $this->configuration;
        foreach ($indexes as $index)
        {
            if (is_array($configuration) && array_key_exists($index, $configuration))
            {
                $configuration = $configuration[$index];
            }
            else
            {
                return $configuration;
            }
        }

        return $configuration;
    }

    public function persistence(): Persistence
    {
        return $this->cachedClassRetrieval('persistence');
    }

    public function db(): Database
    {
        return $this->cachedClassRetrieval('database');
    }

    public function repository(string $class): Repository
    {
        return new ('WS\Repository\\' . $class)($this);
    }

    public function console(): Application
    {
        return $this->cachedClassRetrieval('console', function () {
            $application = new Application();
            $this->commandRegister($application, 'src/WS/Cli/Command');
            return $application;
        });
    }

    /**
     * @param string $category
     * @param string $templateName
     * @param array $parameters
     * @param array $headers
     * @return Response
     */
    public function buildResponse(string $category = Response::DIRECTORY_DEFAULT, string $templateName = 'index', array $parameters = [], array $headers = []): Response
    {
        return new Response($this, $category, $templateName, $parameters, $headers);
    }

    public function tables(): array
    {
        return [
            'location' => [
                'location_id' => [
                    'INT', 'NOT NULL', 'AUTO_INCREMENT', 'PRIMARY KEY'
                ],
                'name' => [
                    'VARCHAR(128)', 'NOT NULL'
                ],
                'organization' => [
                    'VARCHAR(128)', 'NOT NULL'
                ],
                'street_address' => [
                    'VARCHAR(128)', 'NOT NULL'
                ],
                'city' => [
                    'VARCHAR(128)', 'NOT NULL'
                ],
                'state' => [
                    'VARCHAR(2)', 'NOT NULL'
                ],
                'zip_code' => [
                    'VARCHAR(10)', 'NOT NULL'
                ]
            ],
            'school' => [
                'school_id' => [
                    'INT', 'NOT NULL', 'AUTO_INCREMENT', 'PRIMARY KEY'
                ],
                'location_id' => [
                    'INT', 'NOT NULL'
                ],
                'name' => [
                    'VARCHAR(128)', 'NOT NULL'
                ]
            ],
            'administrator' => [
                'school_id' => [
                    'INT', 'NOT NULL'
                ],
                'user_id' => [
                    'INT', 'NOT NULL'
                ],
                'privilege_table' => [
                    'TEXT', 'NOT NULL'
                ]
            ],
            'user' => [
                'user_id' => [
                    'INT', 'NOT NULL', 'AUTO_INCREMENT', 'PRIMARY KEY'
                ]
            ],
            'student' => [
                'user_id' => [
                    'INT', 'NOT NULL'
                ],
                'school_id' => [
                    'INT', 'NOT NULL'
                ],
                'field_data' => [
                    'TEXT', 'NOT NULL'
                ]
            ],
            'authentication' => [
                'user_id' => [
                    'INT', 'NOT NULL', 'PRIMARY KEY'
                ],
                'password_hash' => [
                    'VARCHAR(255)', 'NOT NULL'
                ]
            ],
            'authenticated' => [
                'user_id' => [
                    'INT', 'NOT NULL'
                ],
                'token' => [
                    'VARCHAR(255)', 'NOT NULL'
                ]
            ]
        ];
    }

    /**
     * @param string $identifier
     * @param Closure|bool $creator
     * @return mixed
     */
    protected function cachedClassRetrieval(string $identifier, Closure|bool $creator = true): mixed
    {
        if ($this->objectCache->offsetExists($identifier))
        {
            return $this->objectCache->offsetGet($identifier);
        }

        if ($creator === true)
        {
            $created = $this->classCreator($identifier);
            $this->objectCache->offsetSet($identifier, $created);
        }
        else
        {
            $this->objectCache->offsetSet($identifier, $creator($this));
        }

        return $this->objectCache->offsetGet($identifier);
    }

    /**
     * @param string $identifier
     * @return mixed
     */
    protected function classCreator(string $identifier): mixed
    {
        return match ($identifier) {
            'router' => new Router($this),
            'request' => Request::initialize(),
            'persistence' => new ('WS\Persistence\\' . ucfirst($this->configuration['persistence']['type']))($this),
            'database' => new Database($this->getConfigurationOption('database')),
            default => new ($identifier)($this),
        };
    }

    protected function getConfig(string $env): array
    {
        $configurationContents = file_get_contents("src/config.$env.json");
        return json_decode($configurationContents, true);
    }

    protected function commandRegister(Application $application, string $path)
    {
        foreach (scandir($path) as $outerFile) {
            if (in_array($outerFile, ['.', '..']))
                continue;

            if (is_dir("$path/$outerFile"))
            {
                foreach (scandir("$path/$outerFile") as $innerFile)
                {
                    if (in_array($innerFile, ['.', '..']))
                        continue;

                    if (!is_file("$path/$outerFile/$innerFile"))
                        continue;
                    $innerFile = basename($innerFile, '.php');
                    $className = "WS\\Cli\\Command\\$outerFile\\$innerFile";

                    if (class_exists($className))
                    {
                        $application->add(new ("WS\\Cli\\Command\\$outerFile\\$innerFile")());
                    }
                }
            }
        }
    }

}