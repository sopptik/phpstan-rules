<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\NoModifyAndReturnSelfObjectRule;

use Iterator;
use PHPStan\Rules\Rule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;
use Symplify\PHPStanRules\Rules\NoModifyAndReturnSelfObjectRule;

/**
 * @extends AbstractServiceAwareRuleTestCase<NoModifyAndReturnSelfObjectRule>
 */
final class NoModifyAndReturnSelfObjectRuleTest extends AbstractServiceAwareRuleTestCase
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
        yield [__DIR__ . '/Fixture/SkipUnnesting.php', []];
        yield [__DIR__ . '/Fixture/SkipAnotherType.php', []];
        yield [__DIR__ . '/Fixture/SkipNoReturnNoExpr.php', []];
        yield [__DIR__ . '/Fixture/SkipReturnClone.php', []];
        yield [__DIR__ . '/Fixture/SkipNotReturnObject.php', []];
        yield [__DIR__ . '/Fixture/SkipStringUnion.php', []];
        yield [__DIR__ . '/Fixture/SkipNodeTraverser.php', []];

        yield [__DIR__ . '/Fixture/ModifyAndReturnSelfObject.php', [
            [NoModifyAndReturnSelfObjectRule::ERROR_MESSAGE, 14],
        ]];

        yield [__DIR__ . '/Fixture/NoClone.php', [[NoModifyAndReturnSelfObjectRule::ERROR_MESSAGE, 15]]];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            NoModifyAndReturnSelfObjectRule::class,
            __DIR__ . '/../../../config/symplify-rules.neon'
        );
    }
}
