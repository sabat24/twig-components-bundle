<?php

namespace Olveneer\TwigComponentsBundle\Twig\tag;

use Twig\Error\SyntaxError;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Class SlotTokenParser
 *
 * @package Olveneer\TwigComponentsBundle\Slot
 */
class ComponentParser extends AbstractTokenParser
{
    private string $endTag = 'endget';

    public function parse(Token $token): ComponentNode
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        [$variables, $slotted] = $this->parseArguments();

        return new ComponentNode($expr, $variables, $token->getLine(), $slotted, $this->getTag());
    }

    /**
     * @throws SyntaxError
     */
    protected function parseArguments(): array
    {
        $stream = $this->parser->getStream();

        $variables = null;

        if ($stream->nextIf(/* Twig_Token::NAME_TYPE */ 5, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(/* Twig_Token::BLOCK_END_TYPE */ 3);

        $body = $this->parser->subparse([$this, 'decideComponentFork']);

        $slotted = [];
        $end = false;
        while (!$end) {
            switch ($stream->next()->getValue()) {
                case 'slot':
                    $name = $stream->getCurrent()->getValue();
                    $stream->expect(Token::NAME_TYPE);

                    $stream->expect(/* Twig_Token::BLOCK_END_TYPE */ 3);
                    $slotNodes = $this->parser->subparse([$this, 'decideComponentFork']);

                    $slotted[$name] = $slotNodes;
                break;

                case 'endslot':
                    $stream->expect(/* Twig_Token::BLOCK_END_TYPE */ 3);
                    $body = $this->parser->subparse([$this, 'decideComponentFork']);
                break;

                case $this->endTag:
                    $end = true;
                break;

                default:
                    throw new SyntaxError(sprintf('Unexpected end of template. Twig was looking for the following tag "else", "elseif", or "endif" to close the "if" block started at line %d).', $stream->getCurrent()
                        ->getLine()), $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
        }

        $stream->expect(/* Twig_Token::BLOCK_END_TYPE */ 3);

        return [$variables, $slotted];
    }

    public function getTag(): string
    {
        return 'get';
    }

    /**
     * Callback called at each tag name when subparsing, must return
     * true when the expected end tag is reached.
     *
     */
    public function decideComponentEnd(Token $token): bool
    {
        return $token->test([$this->endTag]);
    }

    /**
     * Callback called at each tag name when subparsing, must return
     * true when the expected end tag is reached.
     *
     */
    public function decideComponentFork(Token $token): bool
    {
        return $token->test(['slot', 'endslot', $this->endTag]);
    }
}
