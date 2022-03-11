<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\Complexity\ForbiddenComplexForeachIfExprRule\Fixture;

class WithMethodCallWithParameter
{
    public function getData($arg)
    {
        return [];
    }

    public function execute($arg)
    {
        foreach ($this->getData($arg) as $key => $item) {

        }
    }

}
