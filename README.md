# EKO Instruments Design System

Buttons, form fields, cards and other components in the EKO house style, ready to use in your PHP application.
This guide gets you from an empty project to a styled page in about five minutes.

## Requirements

- PHP 8.0 or newer
- [Composer](https://getcomposer.org/)

## 1. Install the package

The package is installed straight from GitHub. In your project folder, run:

```bash
composer config repositories.eko-design-system vcs https://github.com/Palundy/EKO-Design-System
composer require eko-instruments/design-system:dev-main
```

## 2. Publish the CSS, JavaScript, fonts and icons

Your web server can't serve files from `vendor/`, so the design system copies its files into a folder of your website.
Add this to your project's `composer.json`, so it happens automatically after every install and update:

```json
{
    "extra": {
        "eko-design-system": {
            "public-dir": "public/design-system"
        }
    },
    "scripts": {
        "post-install-cmd": ["EKO\\DesignSystem\\AssetPublisher::composerPublish"],
        "post-update-cmd": ["EKO\\DesignSystem\\AssetPublisher::composerPublish"]
    }
}
```

`public-dir` is relative to your project root. Use a folder of its own: it is emptied and refilled on every run.

Then run:

```bash
composer install
```

You should see `EKO design system: published … files to public/design-system`.
Add `public/design-system/` to your `.gitignore`, just like `vendor/`.

## 3. Build your first page

Create `public/index.php`:

```php
<?php
require __DIR__ . '/../vendor/autoload.php';

use EKO\DesignSystem\DesignSystem;
use EKO\DesignSystem\Button;
use EKO\DesignSystem\Fields\Input;

// The URL of the folder from step 2, as seen from the browser
DesignSystem::setBaseUrl('/design-system');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello EKO</title>
    <?= DesignSystem::stylesheet() ?>
    <?= DesignSystem::scripts() ?>
</head>
<body>
    <form method="post">
        <?php
        new Input("Email address", name: "email", type: "email", required: true);
        new Button("Send", type: "submit", icon: "envelope-outline");
        ?>
    </form>
</body>
</html>
```

## 4. Run it

```bash
php -S localhost:8000 -t public
```

Open <http://localhost:8000>. You now have an EKO input field and button.

## The basics in four rules

1. **Creating a component prints it.** `new Button("Save")` outputs the HTML right where you write it, so you don't need `echo`.
2. **Pass options by name.** `new Button("Delete", style: "destructive", state: "loading")`. Everything except the first argument is optional.
3. **Pass plain text.** All text, URLs and attributes are escaped for you, so user input is safe and you don't need to escape it yourself.
4. **Icons go by name.** `icon: "envelope-outline"` uses one of the ~1,600 bundled icons (see `public/design-system/assets/icons/` for the names). A misspelled name throws an exception instead of showing an empty square.

## Available components

Import each one with its `use` line:

| Component | Import |
|---|---|
| Button | `use EKO\DesignSystem\Button;` |
| Section heading | `use EKO\DesignSystem\SectionHeading;` |
| Spec table | `use EKO\DesignSystem\SpecTable;` |
| Input, Textarea, Dropdown | `use EKO\DesignSystem\Fields\Input;` (and `Textarea`, `Dropdown`) |
| Product, contact, application, industry and article cards | `use EKO\DesignSystem\Cards\ProductCard;` (and `ContactCard`, `ApplicationCard`, `IndustryCard`, `ArticleCard`) |
| Breadcrumb, navigation item | `use EKO\DesignSystem\Navigation\Breadcrumb;` (and `NavItem`) |

Every argument is documented in the class's docblock, so your editor shows it as you type.

## Updating

```bash
composer update eko-instruments/design-system
```

The published files are refreshed automatically.

## See every component

Clone this repository, run `composer install` and `php -S localhost:8000` in it, and open <http://localhost:8000/showcase.php>.
It shows every component in all of its states, with the code to use it.
