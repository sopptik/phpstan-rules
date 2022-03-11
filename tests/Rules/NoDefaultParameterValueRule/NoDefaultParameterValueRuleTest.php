<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\NoDefaultParameterValueRule;

use Iterator;
use PHPStan\Rules\Rule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;
use Symplify\PHPStanRules\Rules\NoDefaultParameterValueRule;

/**
 * @extends AbstractServiceAwareRuleTestCase<NoDefaultParameterValueRule>
 */
final class NoDefaultParameterValueRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param array<string|int> $expectedErrorMessagesWithLines
     */
    public function testRule(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    /**
     * @return Iterator<array<string|int[]|string[]>>
     */
    public function provideData(): Iterator
    {
        $errorMessage = sprintf(NoDefaultParameterValueRule::ERROR_MESSAGE, 'value');
        yield [__DIR__ . '/Fixture/MethodWithDefaultParamValue.php', [[$errorMessage, 9]]];

        yield [__DIR__ . '/Fixture/SkipParentContract.php', []];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            NoDefaultParameterValueRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}
