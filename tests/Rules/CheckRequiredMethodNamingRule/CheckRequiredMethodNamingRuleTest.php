<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\CheckRequiredMethodNamingRule;

use Iterator;
use PHPStan\Rules\Rule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;
use Symplify\PHPStanRules\Rules\CheckRequiredMethodNamingRule;

/**
 * @extends AbstractServiceAwareRuleTestCase<CheckRequiredMethodNamingRule>
 */
final class CheckRequiredMethodNamingRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @dataProvider provideData()
     * @param string[] $filePaths
     * @param array<int|string> $expectedErrorMessagesWithLines
     */
    public function testRule(array $filePaths, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse($filePaths, $expectedErrorMessagesWithLines);
    }

    public function provideData(): Iterator
    {
        yield [[__DIR__ . '/Fixture/SkipWithoutRequired.php'], []];
        yield [[__DIR__ . '/Fixture/SkipAutowireName.php'], []];
        yield [[__DIR__ . '/Fixture/SkipWithInjectAttributeCorrect.php'], []];
        yield [[__DIR__ . '/Fixture/SkipWithInjectAttribute.php'], []];

        $errorMessage = sprintf(CheckRequiredMethodNamingRule::ERROR_MESSAGE, 'run');

        yield [[__DIR__ . '/Fixture/WithInject.php'], [[$errorMessage, 12]]];
        yield [[__DIR__ . '/Fixture/WithRequiredNotAutowire.php'], [[$errorMessage, 12]]];
        yield [[__DIR__ . '/Fixture/WithRequiredAttributeNotAutowire.php'], [[$errorMessage, 11]]];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            CheckRequiredMethodNamingRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}
