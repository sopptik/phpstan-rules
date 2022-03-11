<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\ForbiddenTestsNamespaceOutsideTestsDirectoryRule;

use Iterator;
use PHPStan\Rules\Rule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;
use Symplify\PHPStanRules\Rules\ForbiddenTestsNamespaceOutsideTestsDirectoryRule;

/**
 * @extends AbstractServiceAwareRuleTestCase<ForbiddenTestsNamespaceOutsideTestsDirectoryRule>
 */
final class ForbiddenTestsNamespaceOutsideTestsDirectoryRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param array<string|int> $expectedErrorsWithLines
     */
    public function testRule(string $filePath, array $expectedErrorsWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorsWithLines);
    }

    /**
     * @return Iterator<array<int, mixed[]|string>>
     */
    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/tests/SkipTestsNamespaceInsideTestsDirectoryClass.php', []];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            ForbiddenTestsNamespaceOutsideTestsDirectoryRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}
