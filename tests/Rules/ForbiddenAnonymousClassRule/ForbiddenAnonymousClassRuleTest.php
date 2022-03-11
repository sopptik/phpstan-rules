<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\ForbiddenAnonymousClassRule;

use Iterator;
use PHPStan\Rules\Rule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;
use Symplify\PHPStanRules\Rules\ForbiddenAnonymousClassRule;

/**
 * @extends AbstractServiceAwareRuleTestCase<ForbiddenAnonymousClassRule>
 */
final class ForbiddenAnonymousClassRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param mixed[] $expectedErrorMessagesWithLines
     */
    public function testRule(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/SkipDedicatedClass.php', []];
        yield [__DIR__ . '/Fixture/AnonymousClass.php', [[ForbiddenAnonymousClassRule::ERROR_MESSAGE, 11]]];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            ForbiddenAnonymousClassRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}
