CHANGELOG
===================
## v2.3.0 - (2024-06-10)
### Added
- `AbstractApiCallAdmin::getOriginChoices` for api call origin filter
- `ui_banner.html.twig` macro to prompt a tailwind banner for usefull info (current environment, ...)
- `empty_layout.html.twig` add `ui_banner` on the **sonata_wrapper** block to show current server environment
- `standard_layout.html.twig` add `ui_banner` on the **sonata_header_noscript_warning** block to show current server environment
  - to unlock it add the following to your `twig.globals` config : `smart_server_environment: '%env(default::ENVIRONMENT)%'`
- `admin.en.xlf` add missing english translations
- `RestartApiCallTrait` controller action to recall already monitored api call
  - Dedicated `restart_api_call.html.twig` action template with special `ROLE_MONITORING_RESTART_API_CALL` role check
  - Dedicated admin.extension.action_restart_api_call sonata extension
- `AbstractApiCallAdmin` add `restartedAt` date show view + refresh icon on list to check which api has been recalled

## v2.2.0 - (2024-06-04)
### Changed
- `AbstractAdmin::__construct` params are now all optionnal as we must configure it through tags from what's ask on the next v5 of Sonata Admin
- Update minimal smartbooster/core-bundle requirements to have ProcessMonitor and ApiCallMonitor services

### Added
- Sonata abstract monitoring admin for CRON and ApiCall 
- Sonata admin template for generic fields :
  - `list_nl2br.html.twig`
  - `show_json.html.twig`
  - `show_process_logs.html.twig`
  - `process_status.html.twig`
  - `api_call_status_code.html.twig`

### Fixed
- `ParameterAdmin` fix remove useless translations options on the type enum field

### Removed
- Remove allowing version `^3.3` for `yokai/enum-bundle` bundle because `ParameterTypeEnum` extends `TranslatedEnum` and this is not present in version `^3.3`

## v2.1.1 - (2024-03-28)

### Added
- Add annotations for orm mapping in addition to attributes to be compatible with both implementation

### Fixed
- `BatchLog::date` type to `DATETIME_MUTABLE` (type error introduce in update v2.0.0)
- `UserTrait::lastLogin` type to `DATETIME_MUTABLE` (type error introduce in update v2.0.0)

## v2.1.0 - (2024-03-17)

### Uprade guide

**The upgrade to this version needs some extra steps from your part to work properly. Please do the following :**
- Run a doctrine migration for the new properties added to the Smart Parameter Entity if you use them.
- Also add the following templates on your project if you use our ParameterAdmin
```twig
{# templates/admin/parameter_admin/list_value.html.twig #}
{% extends '@SonataAdmin/CRUD/base_list_field.html.twig' %}
{% block field %}
    {% include '@SmartSonata/admin/parameter_admin/render_value.html.twig' %}
{% endblock %}

{# templates/admin/parameter_admin/timeline_history_field.html.twig #}
{% extends '@SmartSonata/admin/base_field/timeline_history_field.html.twig' %}
{% block render_value %}
    {% include '@SmartSonata/admin/parameter_admin/render_value.html.twig' %}
{% endblock %}
```
- Once this is done, the ParameterAmin should work back as before. Update your smart_sonata.parameters types if you need to and you are good to go.

_Now back to what have changes on this version ..._

### Added
- `ParameterInterface` for a better following on Parameter method and type evolution
  - It extends the `HistorizableInterface` which add the history field to the entity
  - So when upgrading to this version make sure to run a doctrine migration to have your updated values properly logged.
- `ParameterInterface::type` Parameter can now have a type (text by default) which impact the validation and the return type of the getValue
- `ParameterInterface::getArrayValue` for list values and email chain, this method return the value as a proper array type
- `ParameterInterface::regex` used for value validation for text and list parameter type
- Add `yokai/enum-bundle` composer requirement for the ParameterTypeEnum

### Changed
- `ParameterProvider::getValue` now handle every new type added to the `ParameterInterface`
- `ParameterAdmin` impact of new type and regex property added to the `ParameterInterface`
  - Changes made to the parameter value are now logged in the history of the Parameter
  - The help field is now visible on the show/form only if it's not null

## v2.0.1 - (2024-02-22)
### Fixed
- Fix clear cache error on `EmailProvider` locale -> can be nullable and check `$requestStack->getCurrentRequest()` is not null.

## v2.0.0 - (2024-02-19)
### Added
- **Add Symfony v6.4 support**
- Default `nelmio_alice` locale config to **fr_FR**
- `SmartAdminInterface` which is used on AdminCompilerPass to add the required extra services
- Missing admin and security trad added 

### Removed
- **Drop Symfony v4 support**
- Remove `doctrine/annotations` as we now use PHP8 attributes to define ORM properties
- User roles aren't stored in database anymore, each entity with UserInterface must just define the getRoles function

### Changed
- Use `Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface` service instead of `Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface` to be compatible with
SF 6
- Reformat all the entity annotations with PHP8 attributes
- `AbstractAdmin::setTokenManager` is now preset by the new `AdminCompilerPass` (as the services.yaml abstract: true old way don't work properly with
SF 6 and the latest Sonata version)
- The `AbstractAdmin::get` function has been flagged as @deprecated and will be remove next major (instead inject service via construct DI)

### Fixed
- Fix auto margin of the login-box using display flex
 
## v1.5.1 - (2024-01-08)

### Changes

- Use namespace after use core bundle in `PasswordSafeableTrait.php`
- Use bundle `smartbooster/standard-bundle`, changes in :
  - /bin
  - Add configs
  - Deletes make files
  - Add Kernel
  - Add .gitignore content
  - Changes require dev
  - Changes config of phpcs, phpstan and phpunit
  - Comment base test because it doesn't work with the new `smartbooster/standard-bundle` implementation
- Fix some style because of the new `smartbooster/standard-bundle` implementation
- Change action phpunit and qa order and use make, delete cache steps
- Add install.mk instead of use command in Makefile
- Delete codecov scan sending

## v1.5.0 - (2024-01-04)

### Changes

- Stop support for php 7.4
- Add `smartbooster/core-bundle` bundle
- Remove `IniOverrideConfig.php` to use `smartbooster/core-bundle`
- Remove `IsPasswordSafe` validator to use `smartbooster/core-bundle`
- Remove `AbstractFixtures.php` validator to use `smartbooster/core-bundle`
- Remove php 7.4 matrix on action because it can't use `smartbooster/core-bundle` on php 7.4. Matrix implementation stay for future dev
- Change php version on composer for run with >= 8.1

## v1.4.9 - (2024-01-04)

### Changes

- Remove implement `\Serializable` in User
- Add magic method `__serialize` and `__unserialize` instead of `\Serializable` method
- Add class variable of `$databaseTool` and `$client` in `AbstractWebTestCase`
- Add `unset($this->databaseTool);` in `tearDown()` in `AbstractWebTestCase`
- Call controller method with `::` not `:` in routing config
- Add return types
- Change `ROLE_PREVIOUS_ADMIN` to `IS_IMPERSONATOR`
- `Configuration::getConfigTreeBuilder` must return type `TreeBuilder`
- Add `Symfony\Component\DependencyInjection\ContainerInterface` in service because use symfony service is deprecated
- Set token storage by injection in `AbstractAdmin`
- User must implement `PasswordAuthenticatedUserInterface`. Add implementation in `SmartUserInterface`
- Add missing method and update method in `UserTrait`
- Set dummy password by default in `UserProcessor`
- Add `symfony/property-info` into composer to fix compatibility issue
- Add `symfony/expression-language` into composer to fix compatibility issue
- Add `symfony/security-core` into composer to fix missing service `@security.user_password_encoder.generic`
- Remove `scrutinizer.yml` because we don't use it

## v1.4.8 - (2023-08-09)

### Added

- Service `IniOverrideConfig` for override php memory limit. If application use it, the env variable `PLATFORM_BATCH_MEMORY` must be set. ex : `PLATFORM_BATCH_MEMORY=1024M`

## v1.4.7 - (2023-07-28)

### Update

- Update composer php version to use ^8.2
- Add possibility to use version ^7.0 of `dama/doctrine-test-bundle`
- Restrict use maximal version `^5.4` of `symfony/security-http` because of changing on `UserPasswordEncoderInterface` in symfony 6
- Add `doctrine/annotations` in the require of composer
- Update codes for phpstan issues
- Update github workflow for run in 7.4 and 8.2

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
