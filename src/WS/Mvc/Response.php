<?php

namespace WS\Mvc;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TemplateWrapper;
use WS\App;
use WS\BaseInitializable;

/**
 * @author Jayson Fong <contact@jaysonfong.org>
 * @copyright Jayson Fong 2022
 */
class Response extends BaseInitializable
{

    const DIRECTORY_DEFAULT = 'default';

    const TEMPLATE_REDIRECT = 'redirect';

    const TYPE_HTML = 0;
    const TYPE_JSON = 1;

    protected string $category;
    protected string $templateName;
    protected array $parameters;
    protected array $headers;
    protected ?array $dataPaths;

    protected int $type = self::TYPE_HTML;

    /**
     * @param App $app
     * @param string $category
     * @param string $templateName
     * @param array $parameters
     * @param array $headers
     */
    public function __construct(App $app, string $category = self::DIRECTORY_DEFAULT, string $templateName = 'index', array $parameters = [], array $headers = [])
    {
        parent::__construct($app);
        $this->category = $category;
        $this->templateName = $templateName;
        $this->parameters = $parameters;
        $this->headers = $headers;

        $this->dataPaths = [
            '__global',
            '__phrase'
        ];
    }

    public function addDataPath(array|string $path): Response
    {
        if (is_array($path))
        {
            $this->dataPaths = array_merge($this->dataPaths, $path);
        }

        $this->dataPaths[] = $path;
        return $this;
    }

    public function setType(int $type = self::TYPE_HTML): Response
    {
        $this->type = $type;
        return $this;
    }

    public function render(): string
    {
        if ($this->type === self::TYPE_JSON)
        {
            return json_encode($this->parameters);
        }

        return $this->templateWrapper()->render($this->getFullParameters());
    }

    public function display(): void
    {
        if ($this->type === self::TYPE_JSON)
        {
            echo json_encode($this->parameters);
            return;
        }

        foreach ($this->headers as $headerName => $headerValue)
        {
            header($headerName . ': ' . $headerValue);
        }
        $this->templateWrapper()->display($this->getFullParameters());
    }

    /**
     * @return TemplateWrapper
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function templateWrapper(): TemplateWrapper
    {
        return $this->app->environment()->load($this->category . DIRECTORY_SEPARATOR . $this->templateName . '.twig.html');
    }

    /**
     * @return array
     */
    protected function getFullParameters(): array
    {
        $parameters = $this->parameters;
        $dataBasePath = $this->app->getConfigurationOption('template', 'dataPath');

        $templateData = array();
        foreach ($this->dataPaths as $dataPath)
        {
            $dataPathFull = $dataBasePath . DIRECTORY_SEPARATOR . $dataPath . '.json';

            if (file_exists($dataPathFull))
            {
                $templateData = array_merge_recursive(
                    $templateData,
                    json_decode(file_get_contents($dataPathFull), true)
                );
            }
        }

        $parameters['__templateData'] = $templateData;

        return $parameters;
    }

}