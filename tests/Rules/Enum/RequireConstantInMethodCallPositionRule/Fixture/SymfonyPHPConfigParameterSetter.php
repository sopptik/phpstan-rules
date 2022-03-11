<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Tests\Rules\Enum\RequireConstantInMethodCallPositionRule\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

final class SymfonyPHPConfigParameterSetter
{
    public function run(ContainerConfigurator $containerConfigurator): void
    {
        $parameters = $containerConfigurator->parameters();
        $parameters->set('key', 'value');
    }
}
