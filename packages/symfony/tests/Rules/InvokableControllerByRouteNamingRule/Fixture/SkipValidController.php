<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Symfony\Tests\Rules\InvokableControllerByRouteNamingRule\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class SkipValidController extends AbstractController
{
    #[Route(path: '/valid', name: 'skipvalid')]
    public function __invoke(): Response
    {
    }
}
