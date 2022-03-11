<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules\Enum;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\PHPStanRules\Matcher\SharedNamePrefixMatcher;
use Symplify\PHPStanRules\Rules\AbstractSymplifyRule;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Rules\Enum\EmbeddedEnumClassConstSpotterRule\EmbeddedEnumClassConstSpotterRuleTest
 */
final class EmbeddedEnumClassConstSpotterRule extends AbstractSymplifyRule implements ConfigurableRuleInterface
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Constants "%s" should be extract to standalone enum class';

    /**
     * @param array<class-string> $parentTypes
     */
    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private SharedNamePrefixMatcher $sharedNamePrefixMatcher,
        private array $parentTypes = [],
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
     * @return mixed[]
     */
    public function process(Node $node, Scope $scope): array
    {
        if ($this->shouldSkip($scope)) {
            return [];
        }

        $classLike = $node->getOriginalNode();
        if (! $classLike instanceof Class_) {
            return [];
        }

        $constantNames = $this->resolveConstantNames($classLike);
        $groupedByPrefix = $this->sharedNamePrefixMatcher->match($constantNames);

        $errorMessages = [];

        foreach ($groupedByPrefix as $prefix => $constantNames) {
            if (\count($constantNames) < 1) {
                continue;
            }

            if ($this->shouldSkipConstantPrefix($prefix)) {
                continue;
            }

            $enumConstantNamesString = \implode('", "', $constantNames);
            $errorMessages[] = \sprintf(self::ERROR_MESSAGE, $enumConstantNamesString);
        }

        return $errorMessages;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [new ConfiguredCodeSample(
            <<<'CODE_SAMPLE'
class SomeProduct extends AbstractObject
{
    public const STATUS_ENABLED = 1;

    public const STATUS_DISABLED = 0;
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
class SomeProduct extends AbstractObject
{
}

class SomeStatus
{
    public const ENABLED = 1;

    public const DISABLED = 0;
}
CODE_SAMPLE
            ,
            [
                'parentTypes' => ['AbstractObject'],
            ]
        )]);
    }

    /**
     * @return string[]
     */
    private function resolveConstantNames(Class_ $class): array
    {
        $constantNames = [];

        foreach ($class->getConstants() as $classConst) {
            /** @var string $constantName */
            $constantName = $this->simpleNameResolver->getName($classConst->consts[0]->name);
            $constantNames[] = $constantName;
        }

        return $constantNames;
    }

    private function shouldSkip(Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

        // already enum
        if (\str_contains($classReflection->getName(), '\\Enum\\') && ! \str_contains(
            $classReflection->getName(),
            '\\Rules\\Enum\\'
        )) {
            return true;
        }

        foreach ($this->parentTypes as $parentType) {
            if ($classReflection->isSubclassOf($parentType)) {
                return false;
            }
        }

        return true;
    }

    private function shouldSkipConstantPrefix(string $prefix): bool
    {
        // constant prefix is needed
        if (! \str_ends_with($prefix, '_')) {
            return true;
        }

        // not enum, but rather validation limit
        if (\str_starts_with($prefix, 'MIN_')) {
            return true;
        }

        return \str_starts_with($prefix, 'MAX_');
    }
}
