<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Nette\Tests\Rules\ValidNetteInjectRule\Fixture;

final class SkipCorrectInject
{
    /**
     * @inject
     * @var SomeType
     */
    public $netteType;
}
