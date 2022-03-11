<?php

declare(strict_types=1);

namespace Symplify\PHPStanRules\Symfony\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\FileAnalyser;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Registry;
use PHPStan\Rules\Rule;
use Symplify\PHPStanRules\ErrorSkipper;
use Symplify\PHPStanRules\Rules\AbstractSymplifyRule;
use Symplify\PHPStanRules\Symfony\NodeAnalyzer\SymfonyRenderWithParametersMatcher;
use Symplify\PHPStanRules\Symfony\ValueObject\RenderTemplateWithParameters;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileSystem;
use Symplify\TemplatePHPStanCompiler\TypeAnalyzer\TemplateVariableTypesResolver;
use Symplify\TwigPHPStanCompiler\TwigToPhpCompiler;

/**
 * @see \Symplify\PHPStanRules\Symfony\Tests\Rules\TwigCompleteCheckRule\TwigCompleteCheckRuleTest
 */
final class TwigCompleteCheckRule extends AbstractSymplifyRule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Complete analysis of PHP code generated from Twig template';

    /**
     * List of errors, that do not bring any value.
     *
     * @var string[]
     */
    private const ERROR_IGNORES = [
        '#Method __TwigTemplate(.*?)::doDisplay\(\) throws checked exception Twig\\\\Error\\\\RuntimeError#',
        '#Call to method (getSourceContext|loadTemplate)\(\) on an unknown class __TwigTemplate(.*?)#',
        '#Use separate function calls with readable variable names#',
        '#Property __TwigTemplate_(.*?)\:\:\$source is never read, only written#',
        '#Separate function "array_merge\(\)" in method call to standalone row to improve readability#',
        '#Function "extract\(\)" cannot be used/left in the code#',
        '#Array method calls \[\$this, "method"\] are not allowed\. Use explicit method instead to help PhpStorm, PHPStan and Rector understand your code#',
        '#Access to property \$(parent|source|macros|blocks) on an unknown class __TwigTemplate_(.*?)#',
        '#Class __TwigTemplate_(.*?) was not found while trying to analyse it \- discovering symbols is probably not configured properly#',
        '#Do not use chained method calls\. Put each on separated lines#',
    ];

    private Registry $registry;

    /**
     * @param Rule[] $rules
     */
    public function __construct(
        array $rules,
        private SymfonyRenderWithParametersMatcher $symfonyRenderWithParametersMatcher,
        private TwigToPhpCompiler $twigToPhpCompiler,
        private SmartFileSystem $smartFileSystem,
        private FileAnalyser $fileAnalyser,
        private ErrorSkipper $errorSkipper,
        private TemplateVariableTypesResolver $templateVariableTypesResolver,
    ) {
        $this->registry = new Registry($rules);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     * @return array<string|Error>
     */
    public function process(Node $node, Scope $scope): array
    {
        // 1. find twig template file path with array
        $renderTemplateWithParameters = $this->symfonyRenderWithParametersMatcher->matchTwigRender($node, $scope);
        if (! $renderTemplateWithParameters instanceof RenderTemplateWithParameters) {
            return [];
        }

        // 2. resolve passed variable types
        $variablesAndTypes = $this->templateVariableTypesResolver->resolveArray(
            $renderTemplateWithParameters->getParametersArray(),
            $scope
        );

        // 3. compile twig to PHP with resolved types in @var docs
        $errors = [];
        foreach ($renderTemplateWithParameters->getTemplateFilePaths() as $templateFilePath) {
            $currentErrors = $this->processTemplateFilePath($templateFilePath, $variablesAndTypes, $scope);
            $errors = array_merge($errors, $currentErrors);
        }

        return $errors;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SomeController extends AbstractController
{
    public function __invoke()
    {
        return $this->render(__DIR__ . '/some_file.twig', [
            'some' => new SomeObject()
        ]);
    }
}

// some_file.twig
{{ some.non_existing_method }}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SomeController extends AbstractController
{
    public function __invoke()
    {
        return $this->render(__DIR__ . '/some_file.twig', [
            'some' => new SomeObject()
        ]);
    }
}

// some_file.twig
{{ some.existing_method }}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return Error[]
     */
    private function processTemplateFilePath(string $templateFilePath, array $variablesAndTypes, Scope $scope): array
    {
        $phpFileContent = $this->twigToPhpCompiler->compileContent($templateFilePath, $variablesAndTypes,);

        // 4. print the content to temporary file
        $tmpFilePath = sys_get_temp_dir() . '/' . md5($scope->getFile()) . '-twig-compiled.php';
        $this->smartFileSystem->dumpFile($tmpFilePath, $phpFileContent);

        // 5. analyse temporary PHP file with full PHPStan rules
        $fileAnalyserResult = $this->fileAnalyser->analyseFile($tmpFilePath, [], $this->registry, null);

        // @todo correct PHP to twig line
        // probably via data in getDebugInfo() method
        return $this->errorSkipper->skipErrors($fileAnalyserResult->getErrors(), self::ERROR_IGNORES);
    }
}
