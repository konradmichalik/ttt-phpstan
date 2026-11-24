# ttt PHPStan rules

PHPStan rules for [ttt](https://github.com/konradmichalik/ttt): tests that set state by hand get pointed to the attribute that does the same and restores it afterwards.

```bash
composer require konradmichalik/ttt-phpstan --dev
```

With [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer) the rules are active right away. Otherwise include `vendor/konradmichalik/ttt-phpstan/rules.neon`.

## Rules

| Identifier | Reported when | Use instead |
|------------|---------------|-------------|
| `ttt.manualGlobals` | `$GLOBALS['TYPO3_CONF_VARS']` is assigned | `#[WithTypo3ConfVars]` |
| `ttt.manualGlobals` | `$GLOBALS['TCA']` is assigned | `#[WithTca]` |
| `ttt.manualEnvVar` | `putenv()` is called | `#[WithEnvVar]` |

A rule only reports when its attribute exists in the project, so older ttt versions never get a pointer to something they do not have (`#[WithTca]` needs ttt 0.4 or newer, the others work with 0.1 and newer). Without ttt installed the rules stay silent.

Only classes that extend PHPUnit's `TestCase` are checked. `FunctionalTestCase` is skipped, because ttt rejects some attributes there.

A test class or method that already carries the matching attribute is not reported: ttt restores the whole global afterwards, so more state inside that test is safe. Replacing the whole global (`$GLOBALS['TYPO3_CONF_VARS'] = [...]`) is never reported: the attributes deep-merge, so they would change what such a test does.

`$GLOBALS['BE_USER']` is not checked. Tests there usually assign a mock with expectations, which `#[WithBackendUser]` cannot replace.

## Development

```bash
composer test   # PHPUnit
composer lint   # composer normalize (dry run) and editorconfig check
composer fix
```

## License

GPL-3.0-or-later
