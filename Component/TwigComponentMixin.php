<?php

namespace Olveneer\TwigComponentsBundle\Component;

use Symfony\Component\OptionsResolver\OptionsResolver;

/***
 * Class TwigComponentMixin
 * @package Olveneer\TwigComponentsBundle\Component
 *
 * A mixin is called when a component is rendered and alters the props and parameters.
 */
class TwigComponentMixin
{

    /**
     * Merges with the parameters.
     */
    public function getParameters(array $props = []): array
    {
        return [];
    }

    /**
     * Merges with the props.
     */
    public function getProps(): array
    {
        return [];
    }

    /**
     * The execution order of all the mixins. Mixins with the same key override the earlier ones.
     * Lower goes first.
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * Configures the props using the Symfony OptionResolver
     *
     * @param OptionsResolver $resolver
     * @return void|bool
     */
    public function configureProps(OptionsResolver $resolver)
    {
        return false;
    }
}
