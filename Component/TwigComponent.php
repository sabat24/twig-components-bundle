<?php

namespace Olveneer\TwigComponentsBundle\Component;

use Olveneer\TwigComponentsBundle\Exception\ComponentNotFoundException;
use Olveneer\TwigComponentsBundle\Exception\MixinNotFoundException;
use Olveneer\TwigComponentsBundle\Exception\TemplateNotFoundException;
use Olveneer\TwigComponentsBundle\Service\SlotsResolver;
use Olveneer\TwigComponentsBundle\Service\ComponentRenderer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TwigComponent
 * @package Olveneer\TwigComponentsBundle\Component
 */
class TwigComponent implements TwigComponentInterface
{

    private string $componentsRoot;

    private array $props;

    private ComponentRenderer $renderer;

    /**
     * Returns the parameters to be used when rendering the template.
     * Props can be provided when rendering the component to make it more dynamic.
     *
     */
    public function getParameters(array $props = []): array
    {
        return $props;
    }

    /**
     *  Returns a string to use as a name for the component.
     *
     */
    public function getName(): string
    {
        $className = get_class($this);
        $forwardSlashed = str_replace('\\', '/', $className);

        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', lcfirst(basename($forwardSlashed)), $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    /**
     * Returns the template file name for the component.
     *
     */
    public function getTemplateName(): string
    {
        return $this->getName() . ".html.twig";
    }

    /**
     * Returns the entire path of the component template location.
     *
     */
    public function getTemplatePath(): string
    {
        return $this->getTemplateDirectory() . '/' . $this->getTemplateName();
    }

    /**
     * Returns the directory the template file is located in
     *
     */
    public function getTemplateDirectory(): string
    {
        return $this->getComponentsRoot();
    }

    /**
     * Returns the base response to use when rendering the component via the render() method.
     *
     */
    public function getRenderResponse(): Response
    {
        return new Response();
    }

    /**
     * Returns the directory name that holds the component.
     *
     *
     */
    public function getComponentsRoot(): string
    {
        return $this->componentsRoot;
    }

    /**
     * Sets the directory name that holds the component.
     *
     */
    public function setComponentsRoot(string $componentsRoot)
    {
        $this->componentsRoot = $componentsRoot;
    }

    /**
     * Returns the props passed to the component
     *
     */
    public function getProps(): array
    {
        return $this->props;
    }

    /**
     * Sets the props passed to the component
     *
     */
    public function setProps(array $props): void
    {
        $this->props = $props;
    }

    /**
     * Configures the props using the Symfony OptionResolver
     *
     * @return void|false
     */
    public function configureProps(OptionsResolver $resolver)
    {
        return false;
    }

    public function configureSlots(SlotsResolver $resolver)
    {

    }

    /**
     * Injects the renderer into the component for rendering.
     *
     */
    public function setRenderer(ComponentRenderer $componentRenderer): void
    {
        $this->renderer = $componentRenderer;
    }

    /**
     * Returns the rendered html of the component.
     *
     * @throws ComponentNotFoundException
     * @throws TemplateNotFoundException
     * @throws \Throwable
     */
    public function renderComponent(array $props = []): string
    {
        return $this->renderer->renderComponent($this->getName(), $props);
    }

    /**
     * Returns a response holding the html of the component.
     * @throws ComponentNotFoundException
     * @throws TemplateNotFoundException
     * @throws \Throwable
     */
    public function render(array $props = []): Response
    {
        return $this->renderer->render($this->getName(), $props);
    }

    /**
     * Returns an array containing references to the desired mixins.
     *
     * @return array
     */
    public function importMixins(): array
    {
        return [];
    }

    /**
     * Whether the props should automatically be injected into the parameters.
     * The injecting of a prop only happens if it doesn't already exist in the parameters.
     *
     */
    public function appendsProps(): bool
    {
        return true;
    }

    /**
     * Returns the Twig Template in string form instead of a file.
     * Returns false if a file is used.
     *
     * @return string|bool
     */
    public function getContent()
    {
        return false;
    }
}
