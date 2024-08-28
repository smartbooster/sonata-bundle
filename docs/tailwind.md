# Tailwind

This bundle provide some templates based on the tailwindcss framework sush as :
- The `ui_banner` macro from `templates/macros/ui_banner.html.twig` which can nicely display the current server environment base on a twig global var

So to used thoses templates you must enable tailwind on your project.

## Tailwind installation steps

- Add front packages :
```bash
yarn add tailwindcss@^3.2.4 @tailwindcss/typography@^0.5.13 
yarn add --dev autoprefixer@^10.4.13 postcss@^8.4.19 postcss-loader@^7.0.2
```

- Add the following file `postcss.config.js` at the root of your project
```js
module.exports = {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  }
};
```

- Add the following file `tailwind.config.js` at the root of your project
```js
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    'templates/**/*.html.twig',
    'vendor/smartbooster/sonata-bundle/templates/**/*.html.twig',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          transparent: 'var(--primary-transparent)',
          lighter: 'var(--primary-lighter)',
          light: 'var(--primary-light)',
          DEFAULT: 'var(--primary-default)',
          dark: 'var(--primary-dark)',
          darker: 'var(--primary-darker)'
        },
        neutral: {
          transparent: 'var(--neutral-transparent)',
          lighter: 'var(--neutral-lighter)',
          light: 'var(--neutral-light)',
          DEFAULT: 'var(--neutral-default)',
          dark: 'var(--neutral-dark)',
          darker: 'var(--neutral-darker)'
        },
        success: {
          light: 'var(--success-light-color)',
          DEFAULT: 'var(--success-color)',
          dark: 'var(--success-dark-color)',
        },
        warning: {
          light: 'var(--warning-light-color)',
          DEFAULT: 'var(--warning-color)',
          dark: 'var(--warning-dark-color)',
        },
        danger: {
          light: 'var(--danger-light-color)',
          DEFAULT: 'var(--danger-color)',
          dark: 'var(--danger-dark-color)',
        },
        info: {
          light: 'var(--info-light-color)',
          DEFAULT: 'var(--info-color)',
          dark: 'var(--info-dark-color)',
        },
      }
    },
  },
  // https://dev.to/kachkolasa/how-to-use-tailwindcss-bootstrap-in-the-same-project-5ho
  corePlugins: {
    preflight: false,
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
  important: '.sb-tailwind',
  safelist: [
    'bg-primary-transparent',
    'bg-primary-lighter',
    'bg-primary-light',
    'bg-primary',
    'bg-primary-dark',
    'bg-primary-darker',
    'bg-neutral-transparent',
    'bg-neutral-lighter',
    'bg-neutral-light',
    'bg-neutral',
    'bg-neutral-dark',
    'bg-neutral-darker',
    'bg-success-light',
    'bg-success',
    'bg-success-dark',
    'bg-warning-light',
    'bg-warning',
    'bg-warning-dark',
    'bg-danger-light',
    'bg-danger',
    'bg-danger-dark',
    'bg-info-light',
    'bg-info',
    'bg-info-dark',
  ],
}
```

- Add the following line to your `webpack.config.js`
```js
Encore
  // Existing content ...
  
  // Enable postcss for Tailwind
  .enablePostCssLoader((options) => {
    options.postcssOptions = {
      config: './postcss.config.js'
    }
  })
```

- Add the following file `assets\admin\styles\_tailwind_base.scss`
```scss
@layer base {
  /* color palette from custom UI chart with customer prefix */
  /* https://tailwindcss.com/docs/customizing-colors */
  :root {
    /* Pour la configuration tailwind.config.js et la génération de classe automatique */
    --primary-transparent: theme(colors.blue.50);
    --primary-lighter: theme(colors.blue.200);
    --primary-light: theme(colors.blue.300);
    --primary-default: theme(colors.blue.600);
    --primary-dark: theme(colors.blue.700);
    --primary-darker: theme(colors.blue.900);

    /* Couleur neutre utilisé pour les bordures, ombres, constrates ... */
    --neutral-transparent: theme(colors.neutral.50);
    --neutral-lighter: theme(colors.neutral.300);
    --neutral-light: theme(colors.neutral.500);
    --neutral-default: theme(colors.neutral.700);
    --neutral-dark: theme(colors.neutral.800);
    --neutral-darker: theme(colors.neutral.900);

    /* Alert, callout, card */
    --success-color: theme(colors.green.500);
    --success-light-color: theme(colors.green.300);
    --success-dark-color: theme(colors.green.700);
    --warning-color: theme(colors.yellow.500);
    --warning-light-color: theme(colors.yellow.300);
    --warning-dark-color: theme(colors.yellow.700);
    --danger-color: theme(colors.red.600);
    --danger-light-color: theme(colors.red.300);
    --danger-dark-color: theme(colors.red.700);
    --info-color: theme(colors.blue.500);
    --info-light-color: theme(colors.blue.400);
    --info-dark-color: theme(colors.blue.700);
  }

  /* semantic color variables for this project */
  :root {
    /* Button */
    --primary-background-color: var(--primary-default);
    --primary-hover-background-color: var(--primary-dark);
  }
}
```

- And import @tailwind minimal layer on your `admin/main.scss` before your existing content
```scss
@tailwind base;
@tailwind components;
@tailwind utilities;

@import "tailwind_base";

// Existing content ...
```

## How to enable the ui_banner

To display the ui_banner add the following global var to your `config/packages/twig.yaml` config.

```yaml
twig:
    globals:
        smart_server_environment: '%env(default::ENVIRONMENT)%'
```

And define the `ENVIRONMENT` value in your .env according to your server environment.
