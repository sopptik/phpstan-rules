<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\ForbiddenAssignInIfRule;

use Iterator;
use PHPStan\Rules\Rule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;
use Symplify\PHPStanRules\Rules\ForbiddenAssignInIfRule;

/**
 * @extends AbstractServiceAwareRuleTestCase<ForbiddenAssignInIfRule>
 */
final class ForbiddenAssignInIfRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param array<string|int> $expectedErrorMessagesWithLines
     */
    public function testRule(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/SkipAssignBeforeIf.php', []];
        yield [__DIR__ . '/Fixture/SkipAssignAfterIf.php', []];
        yield [__DIR__ . '/Fixture/AssignInsideIf.php', [[ForbiddenAssignInIfRule::ERROR_MESSAGE, 12]]];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            ForbiddenAssignInIfRule::class,
            __DIR__ . '/../../../config/symplify-rules.neon'
        );
    }
}
