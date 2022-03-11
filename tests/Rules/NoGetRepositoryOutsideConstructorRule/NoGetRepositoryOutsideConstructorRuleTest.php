<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\NoGetRepositoryOutsideConstructorRule;

use Iterator;
use PHPStan\Rules\Rule;
use Symplify\PHPStanExtensions\Testing\AbstractServiceAwareRuleTestCase;
use Symplify\PHPStanRules\Rules\NoGetRepositoryOutsideConstructorRule;

/**
 * @extends AbstractServiceAwareRuleTestCase<NoGetRepositoryOutsideConstructorRule>
 */
final class NoGetRepositoryOutsideConstructorRuleTest extends AbstractServiceAwareRuleTestCase
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
        yield [__DIR__ . '/Fixture/SkipNonDoctrineRepository.php', []];
        yield [__DIR__ . '/Fixture/SkipTwoTestRepository.php', []];
        yield [__DIR__ . '/Fixture/SkipTestCase.php', []];

        yield [
            __DIR__ . '/Fixture/OneTestRepository.php',
            [[NoGetRepositoryOutsideConstructorRule::ERROR_MESSAGE, 23]],
        ];
    }

    protected function getRule(): Rule
    {
        return $this->getRuleFromConfig(
            NoGetRepositoryOutsideConstructorRule::class,
            __DIR__ . '/config/configured_rule.neon'
        );
    }
}
