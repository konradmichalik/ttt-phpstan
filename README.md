<div align="center">

# ttt PHPStan rules

[![Tests](https://img.shields.io/github/actions/workflow/status/konradmichalik/ttt-phpstan/tests.yml?label=tests&logo=github)](https://github.com/konradmichalik/ttt-phpstan/actions/workflows/tests.yml)
[![Supported PHP Versions](https://img.shields.io/packagist/dependency-v/konradmichalik/ttt-phpstan/php?logo=php)](https://packagist.org/packages/konradmichalik/ttt-phpstan)

</div>

PHPStan rules for [ttt](https://github.com/konradmichalik/ttt). Tests that set `putenv()` or `$GLOBALS` by hand depend on a `tearDown()` that is skipped on hard errors. ttt restores that state declaratively, and these rules point at the attribute that replaces each manual call.

## 🔥 Installation

### Requirements

- PHP 8.2 to 8.6
- PHPStan 2.1 or newer
- [`konradmichalik/ttt`](https://github.com/konradmichalik/ttt), optional: a rule stays silent for attributes that are not installed

### Composer

[![Packagist](https://img.shields.io/packagist/v/konradmichalik/ttt-phpstan?label=version&logo=packagist)](https://packagist.org/packages/konradmichalik/ttt-phpstan)
[![Packagist Downloads](https://img.shields.io/packagist/dt/konradmichalik/ttt-phpstan?color=brightgreen)](https://packagist.org/packages/konradmichalik/ttt-phpstan)

```bash
composer require konradmichalik/ttt-phpstan --dev
```

## ⚡ Usage

If you have the [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer)
package installed, there is nothing more to do. The [rules](rules.neon) are included
automatically.

### Manual Setup

```yaml
# phpstan.neon

includes:
  - %rootDir%/../../konradmichalik/ttt-phpstan/rules.neon

parameters:
  paths:
    - Tests/Unit
```

## 🧰 Rules

| Identifier | Reported when | Use instead |
|------------|---------------|-------------|
| `ttt.manualGlobals` | `$GLOBALS['TYPO3_CONF_VARS']` is assigned below its root | [`#[WithTypo3ConfVars]`](https://github.com/konradmichalik/ttt/blob/main/docs/attributes/with-typo3-conf-vars.md) |
| `ttt.manualGlobals` | `$GLOBALS['TCA']` is assigned below its root | [`#[WithTca]`](https://github.com/konradmichalik/ttt/blob/main/docs/attributes/with-tca.md) |
| `ttt.manualEnvVar` | `putenv()` is called, to set or to clear a variable | [`#[WithEnvVar]`](https://github.com/konradmichalik/ttt/blob/main/docs/attributes/with-env-var.md) |

```php
// Reported: ttt.manualEnvVar
putenv('MY_EXT_FEATURE=1');

// Instead
#[WithEnvVar('MY_EXT_FEATURE', '1')]
public function resolvesConfiguration(): void {}
```

Only classes that extend PHPUnit's `TestCase` are checked. `FunctionalTestCase` is skipped, because ttt rejects some attributes there.

A few cases are deliberately not reported:

- A test class, one of its parent classes or the test method that already carries the matching attribute. ttt restores the whole global afterwards, so more state inside that test is safe.
- Replacing a whole global, as in `$GLOBALS['TYPO3_CONF_VARS'] = [...]`. The attributes deep-merge, so they would change what such a test does.
- Compound assignments (`??=`, `.=`) and `unset()`. Only plain `=` is checked.
- `$GLOBALS['BE_USER']`. Tests usually assign a mock with expectations there, which `#[WithBackendUser]` cannot replace.
- Attributes that are not installed. `#[WithTca]` needs ttt 0.4 or newer, `#[WithEnvVar]` and `#[WithTypo3ConfVars]` exist since 0.1. Without ttt, all rules stay silent.

## 🛠️ Typical Overrides

A test that needs an invalid value, which a deep merge cannot express, keeps its manual assignment. Ignore the rule inline and keep the reason next to it:

```php
// @phpstan-ignore ttt.manualGlobals (tests an invalid, non-array configuration)
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['my_ext'] = 'not-an-array';
```

Or ignore one file by identifier and path:

```yaml
parameters:
  ignoreErrors:
    -
      identifier: ttt.manualEnvVar
      path: Tests/Unit/Utility/EnvironmentHelperTest.php
```

## 🧑‍💻 Development

```bash
composer test   # PHPUnit
composer lint   # composer normalize (dry run) and editorconfig check
composer fix
```

## ⭐ License

This project is licensed under [GNU General Public License 3.0 (or later)](LICENSE).
