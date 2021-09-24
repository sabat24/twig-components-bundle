<?php

namespace Olveneer\TwigComponentsBundle\Twig\tag;

use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Class SlotParser
 *
 * @package Olveneer\TwigComponentsBundle\Slot
 */
class SlotParser extends AbstractTokenParser
{
    /**
     * @var string
     */
    private $endTag = 'endslot';

    public function parse(Token $token): SlotNode
    {
        $lineno = $token->getLine();

        $stream = $this->parser->getStream();

        // recovers all inline parameters close to your tag name
        $params = array_merge([], $this->getInlineParams($token));

        $continue = true;
        while ($continue) {
            // create subtree until the decideSlotEnd() callback returns true
            $body = $this->parser->subparse([$this, 'decideSlotEnd']);

            $tag = $stream->next()->getValue();

            switch ($tag) {
                case $this->endTag:
                    $continue = false;
                break;
                default:
                    throw new SyntaxError(sprintf("Unexpected end of template. Twig was looking for the following tag '$this->endTag' to close the '$this->endTag' block started at line %d)", $lineno), -1);
            }

            // you want $body at the beginning of your arguments
            array_unshift($params, $body);

            // if the endtag can also contain params, you can uncomment this line:
            // $params = array_merge($params, $this->getInlineParams($token));
            // and comment this one:
            $stream->expect(Token::BLOCK_END_TYPE);
        }

        return new SlotNode(new Node($params), $lineno, $this->getTag());
    }

    /**
     * Recovers all tag parameters until we find a BLOCK_END_TYPE ( %} )
     *
     * @throws SyntaxError
     */
    protected function getInlineParams(Token $token): array
    {
        $stream = $this->parser->getStream();
        $params = [];
        while (!$stream->test(Token::BLOCK_END_TYPE)) {
            $params[] = $this->parser->getExpressionParser()->parseExpression();
        }
        $stream->expect(Token::BLOCK_END_TYPE);

        return $params;
    }

    /**
     * Callback called at each tag name when subparsing, must return
     * true when the expected end tag is reached.
     *
     */
    public function decideSlotEnd(Token $token): bool
    {
        return $token->test([$this->endTag]);
    }

    /**
     * slot: if the parsed tag match the one you put here, your parse()
     * method will be called.
     *
     */
    public function getTag(): string
    {
        return 'slot';
    }
}
