<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\ForbiddenForeachEmptyMissingArrayRule\Fixture;

final class SkipNotEmpty
{
    public function run(array $data)
    {
        foreach ($data as $value) {

        }
    }
}
