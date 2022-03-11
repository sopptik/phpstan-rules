<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Rules\IfNewTypeThenImplementInterfaceRule\IfNewTypeThenImplementInterfaceRuleTest
 */
final class IfNewTypeThenImplementInterfaceRule extends AbstractSymplifyRule implements ConfigurableRuleInterface
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Class must implement "%s" interface';

    /**
     * @param array<string, string> $interfacesByNewTypes
     */
    public function __construct(
        private NodeFinder $nodeFinder,
        private SimpleNameResolver $simpleNameResolver,
        private array $interfacesByNewTypes
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [InClassNode::class];
    }

    /**
     * @param InClassNode $node
     * @return string[]
     */
    public function process(Node $node, Scope $scope): array
    {
        $classLike = $node->getOriginalNode();
        if (! $classLike instanceof Class_) {
            return [];
        }

        $expectedInterface = $this->resolveExpectedInterface($classLike);
        if ($expectedInterface === null) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return [];
        }

        if ($classReflection->implementsInterface($expectedInterface)) {
            return [];
        }

        $errorMessage = sprintf(self::ERROR_MESSAGE, $expectedInterface);
        return [$errorMessage];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeRule
{
    public function run()
    {
        return new ConfiguredCodeSample('...');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeRule implements ConfiguredRuleInterface
{
    public function run()
    {
        return new ConfiguredCodeSample('...');
    }
}
CODE_SAMPLE
                ,
                [
                    'interfacesByNewTypes' => [
                        'ConfiguredCodeSample' => 'ConfiguredRuleInterface',
                    ],
                ]
            ),
        ]);
    }

    private function resolveExpectedInterface(Class_ $class): ?string
    {
        $expectedInterface = null;

        $this->nodeFinder->findFirst($class, function (Node $node) use (&$expectedInterface): bool {
            if (! $node instanceof New_) {
                return false;
            }

            foreach ($this->interfacesByNewTypes as $newType => $interface) {
                if (! $this->simpleNameResolver->isName($node->class, $newType)) {
                    continue;
                }

                $expectedInterface = $interface;
                return true;
            }

            return false;
        });

        return $expectedInterface;
    }
}
