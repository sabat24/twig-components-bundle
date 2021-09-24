<?php

namespace Olveneer\TwigComponentsBundle\Service;

/**
 * Holds the configurations to be injected
 *
 * Class ConfigStore
 *
 * @package Olveneer\TwigComponentsBundle\Service
 */
class ConfigStore
{
    public string $componentDirectory;

    /**
     * ConfigStore constructor.
     */
    public function __construct($componentDirectory)
    {
        $this->componentDirectory = $componentDirectory;
    }

    public function getConfigs(): array
    {
        return get_object_vars($this);
    }
}
