<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\Symfony\CheckUnneededSymfonyStyleUsageRule\Fixture;

use DateTime;

class SkipUseMethodCallNotFromSymfonyStyle
{
    private $dateTime;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function run()
    {
        $this->dateTime->format('c');
    }
}
