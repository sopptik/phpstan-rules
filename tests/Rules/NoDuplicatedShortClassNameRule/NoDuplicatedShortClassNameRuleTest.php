<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\NoDuplicatedShortClassNameRule;

use Iterator;
use PHPStan\Rules\Rule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;
use Symplify\PHPStanRules\Rules\NoDuplicatedShortClassNameRule;
use Symplify\PHPStanRules\Tests\Rules\NoDuplicatedShortClassNameRule\Fixture\Nested\SameShortName;

/**
 * @extends AbstractServiceAwareRuleTestCase<NoDuplicatedShortClassNameRule>
 */
final class NoDuplicatedShortClassNameRuleTest extends AbstractServiceAwareRuleTestCase
{
    /**
     * @param string[] $filePaths
     * @dataProvider provideData()
     * @param array<string|int> $expectedErrorMessagesWithLines
     */
    public function testRule(array $filePaths, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse($filePaths, $expectedErrorMessagesWithLines);
    }

    /**
     * @return Iterator<int, mixed[]>
     */
    public function provideData(): Iterator
    {
        // might be same, but skipped for shallow nesting - see config file
        yield [
            [
                __DIR__ . '/Fixture/SkipAlreadyExistingShortName.php',
                __DIR__ . '/Source/SkipAlreadyExistingShortName.php',
            ],
            [],
        ];

        $errorMessage = sprintf(
            NoDuplicatedShortClassNameRule::ERROR_MESSAGE,
            'SameShortName',
            implode(
                '", "',
                [SameShortName::class,
                    \Symplify\PHPStanRules\Tests\Rules\NoDuplicatedShortClassNameRule\Fixture\Nested\OneMoreNested\SameShortName::class,
                ]
            )
        );

        yield [
            [
                __DIR__ . '/Fixture/Nested/SameShortName.php',
                __DIR__ . '/Fixture/Nested/OneMoreNested/SameShortName.php',
            ],
            [[$errorMessage, 7]],
        ];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            NoDuplicatedShortClassNameRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}
