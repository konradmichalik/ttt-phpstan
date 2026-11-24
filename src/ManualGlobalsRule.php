<?php

declare(strict_types=1);

namespace KonradMichalik\TttPhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Reports `$GLOBALS['TYPO3_CONF_VARS']` and `$GLOBALS['TCA']` set by hand in a test.
 *
 * @implements Rule<Assign>
 */
final class ManualGlobalsRule implements Rule
{
    private const ATTRIBUTES = [
        'TYPO3_CONF_VARS' => 'WithTypo3ConfVars',
        'TCA' => 'WithTca',
    ];

    public function __construct(private readonly TestContext $context) {}

    public function getNodeType(): string
    {
        return Assign::class;
    }

    /**
     * @return list<RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $key = $this->globalsKey($node->var);
        if (null === $key || !isset(self::ATTRIBUTES[$key])) {
            return [];
        }

        // Replacing the whole global is not a deep merge, so a ttt attribute would change what the test does.
        if ($node->var instanceof ArrayDimFetch && $this->isGlobals($node->var->var)) {
            return [];
        }

        $attribute = self::ATTRIBUTES[$key];
        if (!$this->context->isUnitTest($scope) || !$this->context->offers($attribute) || $this->context->isSandboxed($scope, $attribute)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf('$GLOBALS[\'%s\'] is set by hand in a test, use #[%s] instead.', $key, $attribute))
                ->identifier('ttt.manualGlobals')
                ->build(),
        ];
    }

    private function globalsKey(Expr $expr): ?string
    {
        while ($expr instanceof ArrayDimFetch) {
            if ($this->isGlobals($expr->var)) {
                return $expr->dim instanceof String_ ? $expr->dim->value : null;
            }
            $expr = $expr->var;
        }

        return null;
    }

    private function isGlobals(Expr $expr): bool
    {
        return $expr instanceof Variable && 'GLOBALS' === $expr->name;
    }
}
