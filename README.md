# SMARTBOOSTER - Sonata bundle

[![Latest Stable Version](https://poser.pugx.org/smartbooster/sonata-bundle/v/stable)](https://packagist.org/packages/smartbooster/sonata-bundle)
[![Latest Unstable Version](https://poser.pugx.org/smartbooster/sonata-bundle/v/unstable)](https://packagist.org/packages/smartbooster/sonata-bundle)
[![Total Downloads](https://poser.pugx.org/smartbooster/sonata-bundle/downloads)](https://packagist.org/packages/smartbooster/sonata-bundle)
[![License](https://poser.pugx.org/smartbooster/sonata-bundle/license)](https://packagist.org/packages/smartbooster/sonata-bundle)

[![Build Status](https://api.travis-ci.org/smartbooster/sonata-bundle.png?branch=master)](https://travis-ci.org/smartbooster/sonata-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/smartbooster/sonata-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/smartbooster/sonata-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/smartbooster/sonata-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/smartbooster/sonata-bundle/?branch=master)

## Installation

### Add the bundle as dependency with Composer

``` bash
composer require smartbooster/sonata-bundle
```

### Enable the bundle in the kernel

### Add frontend dependencies

``` bash
    yarn add bootstrap-sass --dev
```

### Include styles

    # Copy and customize your colors
    cp smartbooster/sonata-bundle/assets/styles/_variables.scss assets/admin/styles

    # Incluse smart sonata styles in your file (ex: assets/admin/styles/main.scss)
    @import "variables";
    @import 'smartbooster/sonata-bundle/assets/styles/main';

## What's inside !

- default styles for sonata admin


## Contributing

Pull requests are welcome. 

Thanks to [everyone who has contributed](https://github.com/smartbooster/sonata-bundle/contributors) already.
