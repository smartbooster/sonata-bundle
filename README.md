# SMARTBOOSTER - Sonata bundle

[![Latest Stable Version](https://poser.pugx.org/smartbooster/sonata-bundle/v/stable)](https://packagist.org/packages/smartbooster/sonata-bundle)
[![Latest Unstable Version](https://poser.pugx.org/smartbooster/sonata-bundle/v/unstable)](https://packagist.org/packages/smartbooster/sonata-bundle)
[![Total Downloads](https://poser.pugx.org/smartbooster/sonata-bundle/downloads)](https://packagist.org/packages/smartbooster/sonata-bundle)
[![License](https://poser.pugx.org/smartbooster/sonata-bundle/license)](https://packagist.org/packages/smartbooster/sonata-bundle)

![CI workflow](https://github.com/smartbooster/sonata-bundle/actions/workflows/ci.yml/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/smartbooster/sonata-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/smartbooster/sonata-bundle/?branch=master)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/4bfdbbf3b3d14e50b545d5e9d466ade6)](https://www.codacy.com/gh/smartbooster/sonata-bundle/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=smartbooster/sonata-bundle&amp;utm_campaign=Badge_Grade)
[![GitHub contributors](https://img.shields.io/github/contributors/smartbooster/sonata-bundle.svg)](https://github.com/smartbooster/sonata-bundle/graphs/contributors)

[![SymfonyInsight](https://insight.symfony.com/projects/2ae233dc-0bfc-4a3a-a4ab-6b87acdd98ea/small.svg)](https://insight.symfony.com/projects/2ae233dc-0bfc-4a3a-a4ab-6b87acdd98ea)

## Installation

### Add the bundle as dependency with Composer

``` bash
composer require smartbooster/sonata-bundle
```

Put this configuration inside your config/packages/smart_sonata.yaml

``` yaml
smart_sonata:
    sender:
        address: 'project@example.com'
```

[Check full configuration reference here](docs/configuration.md)

### Enable the bundle in the kernel

### Add frontend dependencies

``` bash
    yarn add bootstrap-sass --dev
```

### Include styles

    # Copy and customize your colors
    cp smartbooster/sonata-bundle/assets/styles/_variables.scss assets/admin/styles/_variables.scss

    # Incluse smart sonata styles in your file (ex: assets/admin/styles/main.scss)
    @import "variables";
    @import 'smartbooster/sonata-bundle/assets/styles/main';

## What's inside !

- default styles for sonata admin
- [Mailer](docs/mailer.md) component with an autogenerated documentation of all emails

  Provide an easy way to administrate your app parameters through each environment with the following tools :

*   A **configuration template to define all parameters** that need to exist in your application with their default value
*   A **Command to use on CD to generate missing parameters**
*   A **Parameter Entity** to store your parameters in database
*   An **Admin** to easily edit the value of your parameters and more data related to them

## Contributing

Pull requests are welcome. 

Thanks to [everyone who has contributed](https://github.com/smartbooster/sonata-bundle/contributors) already.

---

*This project is supported by [SmartBooster](https://www.smartbooster.io)*
