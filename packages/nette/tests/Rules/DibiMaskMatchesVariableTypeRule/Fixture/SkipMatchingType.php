<?php

namespace Symplify\PHPStanRules\Nette\Tests\Rules\DibiMaskMatchesVariableTypeRule\Fixture;

final class SkipMatchingType
{
    public function run(\Dibi\Connection $connection)
    {
        $arr = [
            'a' => 'hello',
            'b'  => true,
        ];

        $connection->query('INSERT INTO table %v', $arr);
    }
}
