<?php

namespace Olveneer\TwigComponentsBundle\Component;

use Olveneer\TwigComponentsBundle\Service\ComponentRenderer;
use Olveneer\TwigComponentsBundle\Service\SlotsResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Interface TwigComponentInterface
 * @package Olveneer\TwigComponentsBundle\Service
 */
interface TwigComponentInterface
{
    /**
     * Returns the parameters to be used when rendering the template.
     * Props can be provided when rendering the component to make it more dynamic.
     *
     */
    public function getParameters(array $props = []): array;

    /**
     *  Returns a string to use as a name for the component.
     *
     */
    public function getName(): string;

    /**
     * Configures the props using the Symfony OptionResolver
     *
     * @param OptionsResolver $resolver
     * @return void|bool
     */
    public function configureProps(OptionsResolver $resolver);

    /**
     * Validates the slot content
     *
     * @return mixed
     */
    public function configureSlots(SlotsResolver $resolver);

    /**
     * Returns the template file name for the component.
     *
     */
    public function getTemplateName(): string;

    /**
     * Returns the entire path of the component template location.
     *
     */
    public function getTemplatePath(): string;

    /**
     * Returns the directory the template file is located in
     *
     */
    public function getTemplateDirectory(): string;

    /**
     * Returns the base response to use when rendering the component via the render() method.
     *
     */
    public function getRenderResponse(): Response;

    /**
     * Returns the directory name that holds the component.
     *
     *
     */
    public function getComponentsRoot(): string;

    /**
     * Sets the directory name that holds the component.
     *
     */
    public function setComponentsRoot(string $componentsRoot);

    /**
     * Returns the props passed to the component
     *
     */
    public function getProps(): array;

    /**
     * Sets the props passed to the component
     *
     */
    public function setProps(array $props);

    /**
     * Injects the renderer into the component for rendering.
     *
     */
    public function setRenderer(ComponentRenderer $componentRenderer): void;

    /**
     * Returns a response holding the html of a component.
     *
     */
    public function render(array $props = []): Response;

    /**
     * Returns the rendered html of the component.
     *
     */
    public function renderComponent(array $props = []): string;

    /**
     * Returns an array containing references to the desired mixins.
     *
     */
    public function importMixins(): array;


    /**
     * Whether the props should automatically be injected into the parameters.
     * The injecting of a prop only happens if it doesn't already exist in the parameters.
     *
     */
    public function appendsProps(): bool;

    /**
     * Returns the Twig Template in string form instead of a file.
     * Returns false if a file is used.
     *
     * @return string|bool
     */
    public function getContent();
}
