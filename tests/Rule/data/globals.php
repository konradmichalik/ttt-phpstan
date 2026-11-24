<?php

declare(strict_types=1);

namespace KonradMichalik\TttPhpStan\Tests\Rule\data;

use KonradMichalik\Ttt\Attribute\WithTca;
use KonradMichalik\Ttt\Attribute\WithTypo3ConfVars;
use PHPUnit\Framework\TestCase;

final class PlainTest extends TestCase
{
    public function nested(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['my_ext'] = [];
        $GLOBALS['TCA']['tt_content']['ctrl']['title'] = 'x';
    }

    public function replacingTheWholeGlobalIsNotReported(): void
    {
        $GLOBALS['TYPO3_CONF_VARS'] = ['A' => 1];
        $GLOBALS['TYPO3_CONF_VARS'] = 'not-an-array';
    }

    public function otherKeysAreIgnored(): void
    {
        $GLOBALS['BE_USER'] = null;
        $GLOBALS['LANG'] = null;
        $other = [];
        $other['TCA']['x'] = 1;
    }
}

final class NotATest
{
    public function run(): void
    {
        $GLOBALS['TCA']['x']['y'] = 1;
    }
}

#[WithTypo3ConfVars(['A' => 1])]
final class ClassSandboxedTest extends TestCase
{
    public function confVarsCovered(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['A']['b'] = 1;
    }

    public function tcaNotCovered(): void
    {
        $GLOBALS['TCA']['x']['y'] = 1;
    }
}

final class MethodSandboxedTest extends TestCase
{
    #[WithTca('x', ['ctrl' => []])]
    public function tcaCovered(): void
    {
        $GLOBALS['TCA']['x']['y'] = 1;
    }

    public function tcaOtherMethod(): void
    {
        $GLOBALS['TCA']['x']['y'] = 2;
    }
}

#[WithTca('tt_content', ['ctrl' => []])]
abstract class AttributedBaseTest extends TestCase {}

abstract class InheritingTest extends AttributedBaseTest
{
    public function inheritedFromParent(): void
    {
        $GLOBALS['TCA']['x']['y'] = 1;
    }
}

final class GrandchildTest extends InheritingTest
{
    public function inheritedFromGrandparent(): void
    {
        $GLOBALS['TCA']['x']['y'] = 2;
    }
}

final class UnrelatedSiblingTest extends TestCase
{
    public function notCovered(): void
    {
        $GLOBALS['TCA']['x']['y'] = 3;
    }
}
