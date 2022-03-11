<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Nette\Tests\Rules\NoNetteTemplateVariableReadRule\Fixture;

final class SkipNoControl
{
    public function render()
    {
        if ($this->template->key === 'value') {
            return;
        }
    }
}
