<?php

namespace Olveneer\TwigComponentsBundle\Service;

use Olveneer\TwigComponentsBundle\Exception\ElementMismatchException;
use Olveneer\TwigComponentsBundle\Exception\MissingSlotException;

/**
 * Class SlotsResolver
 *
 * @package Olveneer\TwigComponentsBundle\Service
 */
class SlotsResolver
{
    public array $slots;

    /**
     * @var string[]
     */
    private array $requiredSlots = [];

    /**
     * @param $slots
     * @throws ElementMismatchException
     * @throws MissingSlotException
     */
    public function configure($slots)
    {
        foreach ($this->requiredSlots as $slotName) {
            if (!isset($slots[$slotName])) {
                throw new MissingSlotException("the slot $slotName is required but is never slotted in");
            }
        }

        foreach ($slots as $name => &$slot) {

            $baseOptions = [];
            if (isset($this->slots[$name])) {
                $baseOptions = $this->slots[$name];
            }

            $slot = array_merge(['html' => $slot, 'requiredElements' => []], $baseOptions);
        }

        $pattern = '/<(?<tag>\w+)(?<attribute>(?:\s+\w+(?:\s*=\s*(?:(?:"[^"]*")|(?:\'[^\']*\')|[^>\s]+))?)*)\s*(\/?)>/';

        foreach ($slots as $name => $options) {
            foreach ($options['requiredElements'] as $tag => $elementSpecifications) {
                $matches = [];
                preg_match_all($pattern, $options['html'], $matches);

                $tags = $matches['tag'];
                $attributes = $matches['attribute'];

                $wantedAttributes = $elementSpecifications['attributes'];
                $amount = $elementSpecifications['amount'];

                $found = array_keys($tags, $tag);

                if ($wantedAttributes) {
                    foreach ($found as $key => $val) {
                        $attribute = $attributes[$key];

                        if (!$attribute) {
                            unset($found[$key]);
                        } else {
                            $attributesParts = explode(' ', $attribute);

                            foreach ($attributesParts as $part) {
                                if (!$part) {
                                    continue;
                                }

                                $parts = explode('=', $part);

                                $foundAttribute = false;
                                for ($i = 0; $i < count($parts); $i++) {
                                    $attrKey = $parts[0];
                                    $attrValue = substr($parts[1], 1, -1);

                                    if (isset($wantedAttributes[$attrKey])) {
                                        $wantedValue = $wantedAttributes[$attrKey];

                                        if (is_callable($wantedValue)) {
                                            $check = $wantedValue($attrValue);
                                        } else {
                                            $check = ($wantedAttributes[$attrKey] === $attrValue || $wantedAttributes[$attrKey] === 'any');
                                        }

                                        if ($check) {
                                            $foundAttribute = true;
                                            break;
                                        }
                                    }
                                    // check if the attribute values match or if the wanted attribute is ''.

                                    $i++;
                                }

                                if (!$foundAttribute) {
                                    unset($found[$key]);
                                } else {
                                    break;
                                }
                            }
                        }
                    }
                }

                if (count($found) < $amount) {


                    $pluralTags = 'tag';
                    if ($amount > 1) {
                        $pluralTags = 'tag';
                    }

                    $attributeNotice = '';

                    if (count($wantedAttributes)) {
                        $attributesJson = json_encode($wantedAttributes);

                        $attributeNotice = "containing the $attributesJson attributes";
                    }

                    throw new ElementMismatchException("the content for the slot '$name' does not match the element requirement of $amount <$tag> $pluralTags $attributeNotice");
                }
            }
        }
    }

    public function setRequired(array $slots = [])
    {
        $this->requiredSlots = $slots;
    }

    public function checkSlot($slot): SlotValidatorNode
    {
        return new SlotValidatorNode($slot, $this);
    }
}
