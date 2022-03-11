<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ClassReflection;
use PHPUnit\Framework\TestCase;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PHPStanRules\PhpDoc\PhpDocResolver;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Rules\SeeAnnotationToTestRule\SeeAnnotationToTestRuleTest
 */
final class SeeAnnotationToTestRule extends AbstractSymplifyRule implements ConfigurableRuleInterface
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Class "%s" is missing @see annotation with test case class reference';

    private PrivatesAccessor $privatesAccessor;

    /**
     * @param string[] $requiredSeeTypes
     */
    public function __construct(
        private PhpDocResolver $phpDocResolver,
        private array $requiredSeeTypes
    ) {
        $this->privatesAccessor = new PrivatesAccessor();
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
        $classReflection = $node->getClassReflection();
        if ($this->shouldSkipClassReflection($classReflection)) {
            return [];
        }

        $classLike = $node->getOriginalNode();
        if (! $classLike instanceof Class_) {
            return [];
        }

        $docComment = $node->getDocComment();
        $errorMessage = sprintf(self::ERROR_MESSAGE, $classReflection->getName());
        if (! $docComment instanceof Doc) {
            return [$errorMessage];
        }

        $resolvedPhpDocBlock = $this->phpDocResolver->resolve($scope, $classReflection, $docComment);

        // skip deprectaed
        $deprecatedTags = $resolvedPhpDocBlock->getDeprecatedTag();
        if ($deprecatedTags !== null) {
            return [];
        }

        $seeTags = $this->getSeeTagNodes($resolvedPhpDocBlock);
        if ($this->hasSeeTestCaseAnnotation($seeTags)) {
            return [];
        }

        return [$errorMessage];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass extends Rule
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
/**
 * @see SomeClassTest
 */
class SomeClass extends Rule
{
}
CODE_SAMPLE
                ,
                [
                    'requiredSeeTypes' => ['Rule'],
                ]
            ),
        ]);
    }

    private function shouldSkipClassReflection(ClassReflection $classReflection): bool
    {
        if ($classReflection->isAbstract()) {
            return true;
        }

        foreach ($this->requiredSeeTypes as $requiredSeeType) {
            if ($classReflection->isSubclassOf($requiredSeeType)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param PhpDocTagNode[] $seeTags
     */
    private function hasSeeTestCaseAnnotation(array $seeTags): bool
    {
        foreach ($seeTags as $seeTag) {
            if (! $seeTag->value instanceof GenericTagValueNode) {
                continue;
            }

            if (is_a($seeTag->value->value, TestCase::class, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return PhpDocTagNode[]
     */
    private function getSeeTagNodes(ResolvedPhpDocBlock $resolvedPhpDocBlock): array
    {
        /** @var PhpDocNode $phpDocNode */
        $phpDocNode = $this->privatesAccessor->getPrivateProperty($resolvedPhpDocBlock, 'phpDocNode');

        return $phpDocNode->getTagsByName('@see');
    }
}
