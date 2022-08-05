CHANGELOG for 1.x
===================

## v1.3.0 - (2022-08-05)

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
