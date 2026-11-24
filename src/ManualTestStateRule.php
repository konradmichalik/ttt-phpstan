<?php

declare(strict_types=1);

namespace KonradMichalik\TttPhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Reports test state set by hand where a ttt attribute does the same and restores it.
 *
 * Unit tests only: ttt rejects some attributes on FunctionalTestCase.
 *
 * @implements Rule<Node>
 */
final class ManualTestStateRule implements Rule
{
    private const FUNCTIONAL_TEST_CASE = 'TYPO3\TestingFramework\Core\Functional\FunctionalTestCase';

    private const GLOBALS_ATTRIBUTES = [
        'TYPO3_CONF_VARS' => 'WithTypo3ConfVars',
        'TCA' => 'WithTca',
    ];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly string $attributeNamespace = 'KonradMichalik\\Ttt\\Attribute\\',
    ) {}

    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @return list<RuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Assign && !$node instanceof FuncCall) {
            return [];
        }

        if (!$this->isUnitTestCase($scope)) {
            return [];
        }

        if ($node instanceof FuncCall) {
            return $this->checkPutenv($node);
        }

        return $this->checkGlobalsAssignment($node, $scope);
    }

    /**
     * @return list<RuleError>
     */
    private function checkPutenv(FuncCall $call): array
    {
        if (!$call->name instanceof Name || 'putenv' !== $call->name->toLowerString()) {
            return [];
        }

        if (!$this->isAvailable('WithEnvVar')) {
            return [];
        }

        return [
            RuleErrorBuilder::message('Environment variable set with putenv() in a test, use #[WithEnvVar] instead.')
                ->identifier('ttt.manualEnvVar')
                ->build(),
        ];
    }

    /**
     * @return list<RuleError>
     */
    private function checkGlobalsAssignment(Assign $assign, Scope $scope): array
    {
        $key = $this->globalsKey($assign->var);
        if (null === $key || !isset(self::GLOBALS_ATTRIBUTES[$key])) {
            return [];
        }

        // Replacing the whole global is not a deep merge, so a ttt attribute would change what the test does.
        if ($assign->var instanceof ArrayDimFetch && $this->isGlobalsFetch($assign->var->var)) {
            return [];
        }

        $attribute = self::GLOBALS_ATTRIBUTES[$key];
        if (!$this->isAvailable($attribute) || $this->isSandboxed($scope, $attribute)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf('$GLOBALS[\'%s\'] is set by hand in a test, use #[%s] instead.', $key, $attribute))
                ->identifier('ttt.manualGlobals')
                ->build(),
        ];
    }

    // The attribute restores the whole global afterwards, so setting more state inside such a test is safe.
    private function isSandboxed(Scope $scope, string $attribute): bool
    {
        $name = $this->attributeNamespace . $attribute;

        $class = $scope->getClassReflection();
        if (null !== $class && [] !== $class->getNativeReflection()->getAttributes($name)) {
            return true;
        }

        $function = $scope->getFunction();
        if (null === $class || null === $function || !$class->getNativeReflection()->hasMethod($function->getName())) {
            return false;
        }

        return [] !== $class->getNativeReflection()->getMethod($function->getName())->getAttributes($name);
    }

    // WithTca only exists from ttt 0.4, and without ttt there is nothing to point to.
    private function isAvailable(string $attribute): bool
    {
        return $this->reflectionProvider->hasClass($this->attributeNamespace . $attribute);
    }

    private function globalsKey(Node\Expr $expr): ?string
    {
        while ($expr instanceof ArrayDimFetch) {
            if ($this->isGlobalsFetch($expr->var)) {
                return $expr->dim instanceof String_ ? $expr->dim->value : null;
            }
            $expr = $expr->var;
        }

        return null;
    }

    private function isGlobalsFetch(Node\Expr $expr): bool
    {
        return $expr instanceof Variable && 'GLOBALS' === $expr->name;
    }

    private function isUnitTestCase(Scope $scope): bool
    {
        $class = $scope->getClassReflection();
        if (null === $class || !$class->is(TestCase::class)) {
            return false;
        }

        return !$class->is(self::FUNCTIONAL_TEST_CASE);
    }
}
