<?php

declare(strict_types=1);

namespace KonradMichalik\TttPhpStan;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPUnit\Framework\TestCase;

/**
 * What the rules need to know about the test they are looking at.
 */
final class TestContext
{
    // Unit tests only: ttt rejects some attributes on FunctionalTestCase.
    private const FUNCTIONAL_TEST_CASE = 'TYPO3\TestingFramework\Core\Functional\FunctionalTestCase';

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly string $attributeNamespace = 'KonradMichalik\\Ttt\\Attribute\\',
    ) {}

    public function isUnitTest(Scope $scope): bool
    {
        $class = $scope->getClassReflection();

        return null !== $class && $class->is(TestCase::class) && !$class->is(self::FUNCTIONAL_TEST_CASE);
    }

    // WithTca only exists from ttt 0.4, and without ttt there is nothing to point to.
    public function offers(string $attribute): bool
    {
        return $this->reflectionProvider->hasClass($this->attributeNamespace . $attribute);
    }

    // ttt restores the whole global afterwards, so setting more state inside such a test is safe.
    // It collects attributes from the class, its parents and the test method.
    public function isSandboxed(Scope $scope, string $attribute): bool
    {
        $class = $scope->getClassReflection();
        if (null === $class) {
            return false;
        }

        $name = $this->attributeNamespace . $attribute;
        foreach ([$class, ...$class->getParents()] as $candidate) {
            if ([] !== $candidate->getNativeReflection()->getAttributes($name)) {
                return true;
            }
        }

        $function = $scope->getFunction();
        $native = $class->getNativeReflection();

        return null !== $function
            && $native->hasMethod($function->getName())
            && [] !== $native->getMethod($function->getName())->getAttributes($name);
    }
}
