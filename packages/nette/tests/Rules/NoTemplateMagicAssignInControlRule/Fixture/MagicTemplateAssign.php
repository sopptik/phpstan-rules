<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Nette\Tests\Rules\NoTemplateMagicAssignInControlRule\Fixture;

use Nette\Application\UI\Control;

final class MagicTemplateAssign extends Control
{
    public function render()
    {
        $this->template->key = 'value';
    }
}
