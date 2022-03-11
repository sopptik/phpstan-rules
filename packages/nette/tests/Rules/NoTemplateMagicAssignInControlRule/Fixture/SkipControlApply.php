<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Nette\Tests\Rules\NoTemplateMagicAssignInControlRule\Fixture;

use Nette\Application\UI\Control;

final class SkipControlApply extends Control
{
    public function render()
    {
        $this->template->render('...');
    }
}
