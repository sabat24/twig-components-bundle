<?php

namespace Olveneer\TwigComponentsBundle\Service;

/**
 * Class SlotValidatorNode
 *
 * @package Olveneer\TwigComponentsBundle\Service
 */
class SlotValidatorNode
{

    private SlotsResolver $resolver;

    private string $slot;

    /**
     * SlotValidator constructor.
     */
    public function __construct($slot, $resolver)
    {
        $this->slot = $slot;
        $this->resolver = $resolver;
    }

    public function requiresElement($tag, int $amount = 1, array $attributes = []): SlotsResolver
    {
        $this->resolver->slots[$this->slot]['requiredElements'][$tag] = [
            'attributes' => $attributes,
            'amount' => $amount,
        ];

        return $this->resolver;
    }
}
