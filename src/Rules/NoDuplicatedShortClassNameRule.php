<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Rules;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use Symplify\Astral\Naming\SimpleNameResolver;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Symplify\PHPStanRules\Tests\Rules\NoDuplicatedShortClassNameRule\NoDuplicatedShortClassNameRuleTest
 */
final class NoDuplicatedShortClassNameRule extends AbstractSymplifyRule implements ConfigurableRuleInterface
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Class with base "%s" name is already used in "%s". Use unique name to make classes easy to recognize';

    /**
     * @var string[]
     */
    private const ALLOWED_CLASS_NAMES = [
        '#File$#',
        # per monorepo package unique
        '#Exception$#',
        '#Option#',
        '#InitCommand#',
        # tests
        '#Some#',
        '#GithubApi#',
    ];

    /**
     * @var array<string, string[]>
     */
    private array $declaredClassesByShortName = [];

    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private int $toleratedNestingLevel
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassLike::class];
    }

    /**
     * @param ClassLike $node
     * @return string[]
     */
    public function process(Node $node, Scope $scope): array
    {
        $className = $this->simpleNameResolver->getName($node);
        if ($className === null) {
            return [];
        }

        if ($this->isAllowedClass($className)) {
            return [];
        }

        $shortClassName = $this->simpleNameResolver->resolveShortName($className);

        // make sure classes are unique
        $existingClassesByShortClassName = $this->resolveExistingClassesByShortClassName($shortClassName, $className);
        $this->declaredClassesByShortName[$shortClassName] = $existingClassesByShortClassName;

        $classesByShortName = $this->declaredClassesByShortName[$shortClassName] ?? [];
        if (count($classesByShortName) <= 1) {
            return [];
        }

        // is nesting level tolerated? - e.g. in case of monorepo project, it's ok to have duplicated classes in 2 levels, e.g. Symplify\\CodingStandard\\
        $classesByToleratedNamespace = [];

        $classesByShortNameCount = count($classesByShortName);

        foreach ($classesByShortName as $classByShortName) {
            $toleratedNamespace = Strings::before($classByShortName, '\\', $this->toleratedNestingLevel);
            $classesByToleratedNamespace[$toleratedNamespace][] = $classByShortName;
        }

        $toleratedNamespaces = array_keys($classesByToleratedNamespace);

        // this namespace has many classes tolerated → skip it
        if (count($toleratedNamespaces) >= $classesByShortNameCount) {
            return [];
        }

        $errorMessage = sprintf(self::ERROR_MESSAGE, $shortClassName, implode('", "', $classesByShortName));
        return [$errorMessage];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
namespace App;

class SomeClass
{
}

namespace App\Nested;

class SomeClass
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
namespace App;

class SomeClass
{
}

namespace App\Nested;

class AnotherClass
{
}
CODE_SAMPLE
                ,
                [
                    'toleratedNestingLevel' => 1,
                ]
            ),
        ]);
    }

    private function isAllowedClass(string $name): bool
    {
        // is allowed
        foreach (self::ALLOWED_CLASS_NAMES as $allowedClassName) {
            if (! Strings::match($name, $allowedClassName)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function resolveExistingClassesByShortClassName(string $shortClassName, string $className): array
    {
        $existingClassesByShortClassName = $this->declaredClassesByShortName[$shortClassName] ?? [];
        $existingClassesByShortClassName[] = $className;

        return array_unique($existingClassesByShortClassName);
    }
}
