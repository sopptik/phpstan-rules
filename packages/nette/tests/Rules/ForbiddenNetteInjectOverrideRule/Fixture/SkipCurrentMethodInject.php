<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Nette\Tests\Rules\ForbiddenNetteInjectOverrideRule\Fixture;

final class SkipCurrentMethodInject
{
    /**
     * @var SomeType
     */
    public $someType;

    public function injectThis(AnotherType $anotherType)
    {
        $this->someType = $anotherType;
    }
}
