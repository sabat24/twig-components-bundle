<?php

namespace Olveneer\TwigComponentsBundle\Service;

use Olveneer\TwigComponentsBundle\Component\TwigComponentInterface;
use Olveneer\TwigComponentsBundle\Component\TwigComponentMixin;
use Olveneer\TwigComponentsBundle\Exception\MixinNotFoundException;

/**
 * Class ComponentStore
 *
 * @package App\Olveneer\TwigComponentsBundle\Service
 */
class ComponentStore
{
    /**
     * @var TwigComponentInterface[]
     */
    private array $components = [];

    /**
     * @var TwigComponentMixin[]
     */
    private array $imports = [];

    /**
     * @var TwigComponentMixin[]
     */
    private array $mixins = [];

    /**
     * @param $mixin
     */
    public function registerMixin($mixin)
    {
        $this->mixins[get_class($mixin)] = $mixin;
    }

    /**
     * Adds a component to the store
     *
     * @throws MixinNotFoundException
     */
    public function add(?TwigComponentInterface $component)
    {
        if (!$component instanceof TwigComponentInterface) {
            return;
        }

        $importReferences = $component->importMixins();

        /** @var TwigComponentMixin[] $imports */
        $imports = [];

        foreach ($importReferences as &$import) {
            if (!isset($this->mixins[$import])) {
                throw new MixinNotFoundException("The mixin '$import' is not registered as a mixin.");
            }

            $imports[] = $this->mixins[$import];
        }

        uasort($imports, function (TwigComponentMixin $a, TwigComponentMixin $b) {
            $a = $a->getPriority();
            $b = $b->getPriority();

            if ($a == $b) {
                return 0;
            }

            return ($a > $b) ? -1 : 1;
        });

        $this->imports[$component->getName()] = $imports;
        $this->components[$component->getName()] = $component;
    }

    public function getImports($componentName): ?TwigComponentMixin
    {
        if (!$this->has($componentName)) {
            return null;
        }

        return $this->imports[$componentName];
    }

    /**
     * Returns a component by name or class name.
     */
    public function get($name): ?TwigComponentInterface
    {
        if (class_exists($name)) {
            foreach ($this->components as $component) {
                if (get_class($component) === $name) {
                    return $component;
                }
            }
        }

        if (!$this->has($name)) {
            return null;
        }

        return $this->components[$name];
    }

    /**
     * Checks if the name is registered as a component
     */
    public function has($name): bool
    {
        return isset($this->components[$name]);
    }

    /**
     * Returns a list containing the names of all the registered components.
     */
    public function getRegisteredNames(): array
    {
        return array_keys($this->components);
    }
}
