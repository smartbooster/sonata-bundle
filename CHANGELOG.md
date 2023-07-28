CHANGELOG for 1.x
===================
## v1.4.7 - (2023-07-28)

### Update

- Update composer php version to use ^8.0 and add possibility to use version ^7.0 of `dama/doctrine-test-bundle`
- Update github action to run tests on php 8.2

## v1.4.6 - (2023-07-13)

### Update

- Update bundle version and remove bundle require by other bundle.

### Fixed

- Change `json_array` into `json` orm type on roles attribute in `UserTrait`. Need migration on project.

## v1.4.5 - (2023-07-07)

### Fixed

- Encoder call with test on null password
- Sonata deprecated not use construct parameter in Admin

## v1.4.4 - (2023-03-01)

### Added

- Add new logic check when use HistoryLogger method
- Saving only different value on save diff in HistoryLogger
- Add overriding possibility of display value in diff show of history

## v1.4.3 - (2023-01-27)

### Fixed

- Fixed local in email layout if request is null

## v1.4.2 - (2022-11-10)

### Added

- Improve HistoryLogger with new methods for add diff log
- Add new methods for help to find the diff in HistorizableTrait and HistorizableInterface.

## v1.4.1 - (2022-11-07)

### Added

- [[#41](https://github.com/smartbooster/sonata-bundle/issues/41)] Add HistoryLogger for entity modification logs + default admin template timeline_history

## v1.4.0 - (2022-11-03)

### Added

- [[#41](https://github.com/smartbooster/sonata-bundle/issues/41)] Admin, model and logger for batch logging

## v1.3.3 - (2022-09-22)

### Fixed

- Fixed infinite loop in impersonate.html.twig because of not strict comparison

## v1.3.2 - (2022-08-30)

### Added

- Re add validate method on configureFormOption to ease form validation on the all object

### Fixed

- Fixed Namespace sonata admin extension for UserTrait 

## v1.3.1 - (2022-08-10)

### Fixed

- [[#37](https://github.com/smartbooster/sonata-bundle/issues/37)] Fix UserTrait name setter typping for Phpstan
- [[#31](https://github.com/smartbooster/sonata-bundle/issues/31)] Fix old namespace SmartAuthentication with SmartSonata on templates/email/{locale}
- Fixed missing typing on UserTrait that prevent creating user on Admin 
- Fixed missnaming of NAME_ACTIONS in ActionExtension to keep one column for it

## v1.3.0 - (2022-08-08)

### Added

- [[#32](https://github.com/smartbooster/sonata-bundle/issues/32)] Merge code from the [authentication-bundle](https://github.com/smartbooster/authentication-bundle) and the [parameter-bundle](https://github.com/smartbooster/parameter-bundle) because in practice all smartbooster/sonata project used them. So it will be easier to maintain in just one repo.  

### Changed

- [[#32](https://github.com/smartbooster/sonata-bundle/issues/32)] bumped major version of sonata-project/admin-bundle and doctrine-orm-admin-bundle to ^4.x to allow Symfony 5 compatibility

### Removed

- [[#32](https://github.com/smartbooster/sonata-bundle/issues/32)] the deprecated xxxAction formatted action from AbstractSecurityController have not been kept during the merge bundle migration. 

## v1.2.0 - (2022-05-27)

### Added

- [[#28](https://github.com/smartbooster/sonata-bundle/issues/28)] Automate Mailing management

### Removed

- Drop support for PHP 7.3

## v1.1.4 - (2021-05-04)

### Added

- [[#16](https://github.com/smartbooster/sonata-bundle/issues/16)] Enhance Security by not using Javascript on empty_layout

### Fixed

- [[#18](https://github.com/smartbooster/sonata-bundle/issues/18)] Fix default bundle config loading by using prependExtensionConfig

## v1.1.3 - (2021-04-20)

### Added 

- [[#15](https://github.com/smartbooster/sonata-bundle/issues/15)] Recurrent Config
- [[#14](https://github.com/smartbooster/sonata-bundle/issues/14)] Recurrent Sonata Template

### Changed

- [[#12](https://github.com/smartbooster/sonata-bundle/issues/12)] Migrate to github actions

## v1.1.2 - (2020-11-17)

### Added

- Add common admin bundles : DoctrineFixturesBundle, WebpackEncore, AliceDataFixtures
- Style for vichuploader form field

## v1.1.1 - (2020-01-13)

### Fixed

- Macro alert using raw filter for message translation

## v1.1.0 - (2020-01-06)

### Fixed

- Background display of login screen

### Changed

### Added

- QA tools and assets compilation for testing
