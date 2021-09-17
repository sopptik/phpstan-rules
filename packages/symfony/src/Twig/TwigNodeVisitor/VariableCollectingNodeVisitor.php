<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Symfony\Twig\TwigNodeVisitor;

use Twig\Environment;
use Twig\Node\Expression\NameExpression;
use Twig\Node\ForNode;
use Twig\Node\Node;
use Twig\Node\SetNode;
use Twig\NodeVisitor\NodeVisitorInterface;

final class VariableCollectingNodeVisitor implements NodeVisitorInterface
{
    /**
     * @var string
     */
    private const NAME = 'name';

    /**
     * @var string[]
     */
    private array $variableNames = [];

    /**
     * @var string[]
     */
    private array $dynamicallyCreatedNames = [];

    /**
     * @var string[]
     */
    private array $generatedVariableNames = [];

    /**
     * @param Node<Node> $node
     * @return Node<Node>
     */
    public function enterNode(Node $node, Environment $environment): Node
    {
        if ($node instanceof ForNode) {
            $this->generatedVariableNames[] = $this->getNodeName($node, 'key_target');
            $this->generatedVariableNames[] = $this->getNodeName($node, 'value_target');
            return $node;
        }

        // variables created on-the fly, e.g. with {% set ... %}, in {% for ... %} etc.
        if ($node instanceof SetNode) {
            $namesNode = $node->getNode('names');
            foreach ($namesNode as $nameNode) {
                $this->dynamicallyCreatedNames[] = $nameNode->getAttribute(self::NAME);
            }

            return $node;
        }

        if (! $node instanceof NameExpression) {
            return $node;
        }

        $this->variableNames[] = $node->getAttribute(self::NAME);
        return $node;
    }

    /**
     * @param Node<Node> $node
     * @return Node<Node>|null
     */
    public function leaveNode(Node $node, Environment $environment): ?Node
    {
        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }

    /**
     * @return string[]
     */
    public function getVariableNames(): array
    {
        return array_diff($this->variableNames, $this->generatedVariableNames, $this->dynamicallyCreatedNames);
    }

    /**
     * @param Node<Node> $node
     */
    private function getNodeName(Node $node, string $nodeKey): string
    {
        $keyNode = $node->getNode($nodeKey);
        return $keyNode->getAttribute(self::NAME);
    }
}
