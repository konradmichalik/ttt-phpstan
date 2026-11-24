<?php

declare(strict_types=1);

namespace KonradMichalik\TttPhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Reports `putenv()` in a test.
 *
 * @implements Rule<FuncCall>
 */
final class ManualEnvVarRule implements Rule
{
    public function __construct(private readonly TestContext $context) {}

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @return list<RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Name || 'putenv' !== $node->name->toLowerString()) {
            return [];
        }

        if (!$this->context->isUnitTest($scope) || !$this->context->offers('WithEnvVar')) {
            return [];
        }

        return [
            RuleErrorBuilder::message('Environment variable set or cleared with putenv() in a test, use #[WithEnvVar] instead.')
                ->identifier('ttt.manualEnvVar')
                ->build(),
        ];
    }
}
