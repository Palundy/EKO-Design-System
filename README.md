# EKO Instruments Design System

Buttons, form fields, cards and other components in the EKO house style, ready to use in your **PHP** or **Python (Flask)** application.
Both versions have the same components, the same options and produce exactly the same HTML.
Each quick start gets you from an empty project to a styled page in about five minutes: open the one for your language below.

- [Quick start (PHP and Python)](#quick-start)
- [The basics in four rules](#the-basics-in-four-rules)
- [Available components](#available-components)

---

## Quick Start

<details>
<summary><strong>Show the PHP quick start</strong> (Composer, about five minutes)</summary>

### Requirements

- PHP 8.0 or newer
- [Composer](https://getcomposer.org/)

### 1. Install the package

The package is installed straight from GitHub. In your project folder, run:

```bash
composer config repositories.eko-design-system vcs https://github.com/Palundy/EKO-Design-System
composer require eko-instruments/design-system:^0.1.0
```

`^0.1.0` installs version 0.1.0 or a newer 0.1.x bug-fix release.

### 2. Publish the CSS, JavaScript, fonts and icons

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

### 3. Build your first page

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
        <?= new Input("Email address", name: "email", type: "email", required: true) ?>
        <?= new Button("Send", type: "submit", icon: "envelope-outline") ?>
    </form>
</body>
</html>
```

### 4. Run it

```bash
php -S localhost:8000 -t public
```

Open <http://localhost:8000>. You now have an EKO input field and button.

### Updating

```bash
composer update eko-instruments/design-system
```

This picks up new 0.1.x releases, and the published files are refreshed automatically.
To move to a new minor version such as 0.2, run `composer require eko-instruments/design-system:^0.2.0`.

</details>

<details>
<summary><strong>Show the Python (Flask) quick start</strong> (pip, about five minutes)</summary>

### Requirements

- Python 3.11 or newer
- Flask

### 1. Install the package

The package is installed straight from GitHub, with the CSS, JavaScript, fonts and icons included. In your project's virtual environment, run:

```bash
pip install "eko-design-system[flask] @ git+https://github.com/Palundy/EKO-Design-System@v0.1.0"
```

Or add this line to your `requirements.txt`:

```
eko-design-system[flask] @ git+https://github.com/Palundy/EKO-Design-System@v0.1.0
```

The `@v0.1.0` at the end is the release tag.

### 2. Connect it to your app

There is nothing to copy or publish: the extension serves the design system's files straight from the installed package.
Create `app.py`:

```python
from flask import Flask
from eko_design_system.flask import EkoDesignSystem

app = Flask(__name__)
EkoDesignSystem(app)
```

Using an application factory? Create `design_system = EkoDesignSystem()` at module level and call `design_system.init_app(app)` inside the factory.

### 3. Build your first page

Every template now knows `eko`. Create `templates/index.html`:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello EKO</title>
    {{ eko.stylesheet() }}
    {{ eko.scripts() }}
</head>
<body>
    <form method="post">
        {{ eko.Input("Email address", name = "email", type = "email", required = True) }}
        {{ eko.Button("Send", type = "submit", icon = "envelope-outline") }}
    </form>
</body>
</html>
```

And render it from a route:

```python
from flask import render_template

@app.route("/")
def index():
    return render_template("index.html")
```

### 4. Run it

```bash
flask --app app run
```

Open <http://localhost:5000>. You now have an EKO input field and button.

You can also build components in your Python code and pass them to the template, where `{{ card }}` prints it:

```python
from eko_design_system.cards import ContactCard

return render_template("contact.html", card = ContactCard("Sales", phone = "+31 70 3050117"))
```

### Updating

```bash
pip install --upgrade "eko-design-system[flask] @ git+https://github.com/Palundy/EKO-Design-System@vX.Y.Z"
```

pip can't pick the newest release from GitHub by itself, so replace `vX.Y.Z` with the release you want. Update the tag in your `requirements.txt` as well.

</details>

---

## The basics in four rules

1. **A component is its HTML.** Creating one doesn't print anything; print it where you want it, or keep it as a string:
   ```php
   <?= new Button("Save") ?>                // PHP template
   $html = (string) new Button("Save");     // PHP string
   ```
   ```python
   {{ eko.Button("Save") }}                 {# Jinja template #}
   html = str(Button("Save"))               # Python string
   ```
2. **Pass options by name.** `new Button("Delete", style: "destructive", state: "loading")` in PHP, `Button("Delete", style = "destructive", state = "loading")` in Python.
   Everything except the first argument is optional. Python uses snake_case for names of more than one word (`imageAlt:` in PHP is `image_alt =` in Python).
3. **Pass plain text.** All text, URLs and attributes are escaped for you, so user input is safe and you don't need to escape it yourself.
   In Jinja, components are not escaped a second time.
4. **Icons go by name.** `icon: "envelope-outline"` uses one of the ~1,600 bundled icons (see `assets/icons/` in this repository for the names).
   A misspelled name throws an error instead of showing an empty square.

## Available components

| Component | PHP | Python |
|---|---|---|
| Button | `use EKO\DesignSystem\Button;` | `from eko_design_system import Button` |
| Section heading | `use EKO\DesignSystem\SectionHeading;` | `from eko_design_system import SectionHeading` |
| Spec table | `use EKO\DesignSystem\SpecTable;` | `from eko_design_system import SpecTable` |
| Input, Textarea, Dropdown | `use EKO\DesignSystem\Fields\Input;` | `from eko_design_system.fields import Input` |
| Product, contact, application, industry and article cards | `use EKO\DesignSystem\Cards\ProductCard;` | `from eko_design_system.cards import ProductCard` |
| Breadcrumb, navigation item | `use EKO\DesignSystem\Navigation\Breadcrumb;` | `from eko_design_system.navigation import Breadcrumb` |

In Jinja templates, every component is also available as `eko.<Name>`, such as `{{ eko.ProductCard(title = "MS-80SH") }}`.
Every argument is documented in the class's docblock or docstring, so your editor shows it as you type.

## Addons

Components that only one application needs live in an **addon**, next to the core. Each addon is switched on separately, so other apps don't get its CSS.

| Addon | Components | PHP | Python |
|---|---|---|---|
| EKO Data | Status tracker | `use EKO\DesignSystem\Addons\EkoData\StatusTracker;` | `from eko_design_system.addons.eko_data import StatusTracker` |
| ICF-02 | Sidebar (main menu), pincode sign-in | `use EKO\DesignSystem\Addons\Icf02\Sidebar;`, `…\Pincode;` | `from eko_design_system.addons.icf_02 import Sidebar, Pincode` |

Switch an addon on once, before the page head. `DesignSystem::stylesheet()` and `scripts()` then also print the addon's tags:

```php
use EKO\DesignSystem\DesignSystem;
use EKO\DesignSystem\Addons\EkoData\EkoData;

DesignSystem::enableAddon(EkoData::class);
```

```python
from eko_design_system.addons.eko_data import EkoData

EkoDesignSystem(app, addons = [EkoData])    # Flask: templates use {{ eko.eko_data.StatusTracker(...) }}
DesignSystem.enable_addon(EkoData)          # without Flask
```

The addon files are published and installed with the rest of the package, so you don't need to do anything extra.
An addon's own icons go by name with its prefix, such as `icon: "eko-data:gauge-outline"`.

## See every component

Clone this repository, run `composer install` and `php -S localhost:8000` in it, and open <http://localhost:8000/showcase.php>.
It shows every component in all of its states, with the code to use it.
