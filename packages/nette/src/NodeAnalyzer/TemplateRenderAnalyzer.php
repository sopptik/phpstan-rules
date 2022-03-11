<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use Symplify\Astral\Naming\SimpleNameResolver;

final class TemplateRenderAnalyzer
{
    /**
     * @var string
     */
    private const RENDER = 'render';

    public function __construct(
        private SimpleNameResolver $simpleNameResolver,
        private NetteTypeAnalyzer $netteTypeAnalyzer
    ) {
    }

    public function isNetteTemplateRenderMethodCall(MethodCall $methodCall, Scope $scope): bool
    {
        if (! $this->simpleNameResolver->isNames($methodCall->name, [self::RENDER, 'renderToString', 'action'])) {
            return false;
        }

        return $this->netteTypeAnalyzer->isTemplateType($methodCall->var, $scope);
    }
}
