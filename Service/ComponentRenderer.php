<?php

namespace Olveneer\TwigComponentsBundle\Service;

use Olveneer\TwigComponentsBundle\Exception\ComponentNotFoundException;
use Olveneer\TwigComponentsBundle\Component\TwigComponent;
use Olveneer\TwigComponentsBundle\Component\TwigComponentInterface;
use Olveneer\TwigComponentsBundle\Component\TwigComponentMixin;
use Olveneer\TwigComponentsBundle\Exception\ElementMismatchException;
use Olveneer\TwigComponentsBundle\Exception\MissingSlotException;
use Olveneer\TwigComponentsBundle\Exception\MixinNotFoundException;
use Olveneer\TwigComponentsBundle\Exception\TemplateNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class ComponentRenderer
 *
 * @package Olveneer\TwigComponentsBundle\Service
 */
class ComponentRenderer
{
    private ComponentStore $store;

    private Environment $environment;

    public string $componentDirectory;

    private array $target;

    /**
     * ComponentRenderer constructor.
     */
    public function __construct(ComponentStore $componentStore, Environment $environment, ConfigStore $configStore)
    {
        $this->store = $componentStore;
        $this->environment = $environment;
        $this->componentDirectory = $configStore->componentDirectory;

        $this->target = ['slots' => [], 'context' => []];
    }

    /**
     * Returns the rendered html of a component.
     *
     * @param $name
     * @param array $props
     * @return string
     * @throws ComponentNotFoundException
     * @throws TemplateNotFoundException
     * @throws \Throwable
     */
    public function renderComponent($name, array $props = []): string
    {
        if (!($name instanceof TwigComponentInterface)) {
            $component = $this->getComponent($name, $props);

            if (!($component instanceof TwigComponentInterface)) {
                $this->throwException($name);
            }
        } else {
            $component = $name;
            $name = $component->getName();
        }

        /** @var TwigComponentMixin[] $imports */
        $imports = $this->store->getImports($name);

        $parameters = $component->getParameters($props);

        foreach ($imports as $import) {
            foreach ($import->getParameters($props) as $key => $parameter) {
                $parameters[$key] = $parameter;
            }
        }

        $componentPath = $component->getTemplatePath();

        if ($component->appendsProps()) {
            foreach ($props as $prop => $value) {
                if (!isset($parameters[$prop])) {
                    $parameters[$prop] = $value;
                }
            }
        }

        if ($content = $component->getContent() !== false) {
            $template = $this->environment->createTemplate($content);

            return $template->render($parameters);
        }

        if (!$this->environment->getLoader()->exists($componentPath)) {
            if (substr($componentPath, 0, 1) === '/') {
                $prefix = 'templates';
            } else {
                $prefix = 'templates/';
            }

            $className = get_class($component);

            $errorMsg = "There is no component template found for the '$className' component with the name '$name'.\n Looked for '$prefix$componentPath' but found nothing.";
            throw new TemplateNotFoundException($errorMsg);
        }

        return $this->environment->render($componentPath, $parameters);
    }

    /**
     * Returns a response holding the html of a component.
     *
     * @param $name
     * @param array $props
     * @return Response
     * @throws ComponentNotFoundException
     * @throws TemplateNotFoundException
     * @throws \Throwable
     */
    public function render($name, array $props = []): Response
    {
        $component = $this->getComponent($name, $props);

        $response = $component->getRenderResponse();

        $html = $this->renderComponent($component, $props);

        $response->setContent($html);

        return $response;
    }

    /**
     * @param $name
     * @throws ComponentNotFoundException
     */
    public function throwException($name)
    {
        throw new ComponentNotFoundException("No component for the name '$name' found!'");
    }

    /**
     * @throws ComponentNotFoundException
     */
    public function getComponent($name, array &$props = []): ?TwigComponentInterface
    {
        $component = $this->store->get($name);

        if (!($component instanceof TwigComponentInterface)) {
            $this->throwException($name);
        }

        /** @var TwigComponentMixin[] $imports */
        $imports = $this->store->getImports($name);

        $optionResolver = new OptionsResolver();

        foreach ($imports as $import) {
            foreach ($import->getProps() as $key => $prop) {
                $props[$key] = $prop;
            }

            $import->configureProps($optionResolver);
        }

        $isUsed = $component->configureProps($optionResolver) !== false;

        if ($isUsed) {
            $props = $optionResolver->resolve($props);
        }

        $component->setProps($props);

        return $component;
    }

    /**
     * Initializes the component and adds it to the store
     *
     * @param TwigComponent $component
     * @throws MixinNotFoundException
     */
    public function register(TwigComponent $component)
    {
        $component->setRenderer($this);
        $component->setComponentsRoot($this->componentDirectory);

        $this->store->add($component);
    }

    /**
     * @param $componentName
     * @param $slots
     * @param $context
     * @throws ComponentNotFoundException
     * @throws ElementMismatchException
     * @throws MissingSlotException
     */
    public function openTarget($componentName, $slots, $context)
    {
        $resolver = new SlotsResolver();

        $component = $this->store->get($componentName);

        if (!$component instanceof TwigComponent) {
            throw new ComponentNotFoundException("No component with the name $componentName found when using {% get %}");
        }

        $component->configureSlots($resolver);

        $slots = unserialize($slots);

        $resolver->configure($slots);

        $this->target['slots'] = $slots;
        $this->target['context'] = $context;
    }

    /**
     * @return void
     */
    public function closeTarget()
    {
        $this->target['slots'] = [];
        $this->target['context'] = [];
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getSlot($name)
    {
        return $this->target['slots'][$name];
    }

    public function hasSlot($name): bool
    {
        return isset($this->target['slots'][$name]);
    }

    public function getEnv(): Environment
    {
        return $this->environment;
    }

    public function getContext(): array
    {
        return $this->target['context'];
    }
}
