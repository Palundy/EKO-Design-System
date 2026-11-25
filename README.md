# EKO Instruments Design System

Shared EKO components for web applications, such as buttons, form fields and cards.
The design system is written in plain CSS and vanilla JavaScript, with PHP classes that render the components for you.
It has no build step and no package manager. You copy the folders into your project and include them.

This guide covers using the design system in a **PHP** application.

- [Requirements](#requirements)
- [Installation](#installation)
- [How the PHP components work](#how-the-php-components-work)
- [Components](#components)
  - [Button](#button)
  - [Input](#input)
  - [Textarea](#textarea)
  - [Dropdown](#dropdown)
  - [Product card](#product-card)
  - [Contact card](#contact-card)
- [Icons](#icons)
- [Full example](#full-example)
- [Customising the look](#customising-the-look)
- [Viewing every component](#viewing-every-component)

---

## Requirements

- **PHP 8.0 or newer**, because the components use named arguments. Development uses PHP 8.2.
- The design system's folders must be served from your **web root**. Icon URLs are root-absolute (`/assets/icons/...`).

## Installation

### 1. Copy the folders into your web root

```
your-app/
├── assets/     fonts, icons and images
├── css/        styles (main.css is the only file you link)
├── js/         dropdowns.js (needed for dropdowns)
├── php/        the PHP components
└── your-page.php
```

### 2. Include the styles and script in your page

Add these two lines to `<head>`:

```html
<link rel="stylesheet" href="/css/main.css">
<script src="/js/dropdowns.js" defer></script>
```

`main.css` imports all the other stylesheets, fonts and tokens.
`dropdowns.js` is only needed on pages that use a dropdown.

### 3. Load the PHP components

Put this at the top of your page, then load only the groups you use:

```php
<?php
require 'php/button.php';   // Button
require 'php/fields.php';   // Fields\Input, Fields\Textarea, Fields\Dropdown
require 'php/cards.php';    // Cards\ProductCard, Cards\ContactCard

use Fields\Input;
use Fields\Textarea;
use Fields\Dropdown;
use Cards\ProductCard;
use Cards\ContactCard;
?>
```

## How the PHP components work

Every component behaves the same way:

1. **Creating a component prints it.** `new Button("Save")` echoes the HTML at that spot in your template, so you don't need `echo`.
   ```php
   <form>
       <?php new Input("Email", name: "email", type: "email"); ?>
       <?php new Button("Send", type: "submit"); ?>
   </form>
   ```
2. **Use named arguments.** Only the first argument is positional, and everything else is optional, so pass just what you need by name:
   `new Dropdown("Country", name: "country", options: $countries, required: true);`
3. **Invalid values throw an exception.** A misspelled style or state throws an `InvalidArgumentException` instead of rendering a broken component.
4. **The HTML is also kept on the object** in `->html`. Because the constructor already echoes it, capture it with output buffering if you need it as a string without printing it:
   ```php
   ob_start();
   $button = new Button("Save");
   ob_end_clean();
   $html = $button->html;
   ```

> **Escaping:** every component escapes all the text, URLs and attribute values you pass, so it is safe to pass user input directly.
> Don't escape it yourself first, or characters like `&` will show up as `&amp;`. The flip side is that you can't pass HTML: tags are shown as plain text.

---

## Components

### Button

```php
new Button("Save");
new Button("Delete", style: "destructive");
new Button("Send", type: "submit", icon: "/assets/icons/envelope-outline.svg");
new Button("Saving…", state: "loading");
new Button("Cancel", state: "negative", attributes: ["id" => "cancel-btn"]);
```

| Argument      | Default    | Description |
|---------------|------------|-------------|
| `$label`      | (required) | The button text. |
| `style:`      | `null`     | `null` (primary), `"destructive"`, `"positive"` or `"enquiry"`. |
| `state:`      | `null`     | `null`, `"disabled"`, `"negative"` (outlined) or `"loading"` (spinner). |
| `icon:`       | `null`     | URL of an SVG icon, shown before the label. Hidden while loading. |
| `type:`       | `"button"` | The HTML type: `"button"`, `"submit"` or `"reset"`. |
| `attributes:` | `[]`       | Extra HTML attributes, e.g. `["id" => "send-btn"]`. `true` writes a bare attribute, and `false`/`null` leaves the attribute out. Use `state:` for disabled, negative and loading. |

### Input

A labelled text field with optional info text below it.

```php
new Input("Email address", name: "email", type: "email", required: true);
new Input("Phone", name: "phone", type: "tel", placeholder: "+31 6 12345678");
new Input("Email address", name: "email", value: "wrong@", state: "error", info: "Please enter a valid email address.");
```

| Argument       | Default    | Description |
|----------------|------------|-------------|
| `$label`       | (required) | The label text. ` *` is added when the field is required. |
| `name:`        | `null`     | The form field name. Also used as the id if `id:` is not given. |
| `id:`          | `name`     | The element id. Either `name:` or `id:` is required. |
| `type:`        | `"text"`   | Any HTML input type: `"email"`, `"tel"`, `"number"`, `"password"`, … |
| `placeholder:` | `null`     | Placeholder text. |
| `value:`       | `null`     | The initial value. |
| `required:`    | `false`    | Adds the `required` attribute. |
| `state:`       | `null`     | `"error"` (red), `"success"` (green) or `"disabled"`. |
| `info:`        | `null`     | Help or validation message shown below the field. Screen readers announce it with the field. |
| `attributes:`  | `[]`       | Extra attributes on the `<input>`, e.g. `["autocomplete" => "email"]`. `true` writes a bare attribute, and `false`/`null` leaves the attribute out. |

### Textarea

Takes the same arguments as `Input`, except `type:`. `value:` is the initial text.

```php
new Textarea("Message", name: "message", placeholder: "How can we help?", attributes: ["rows" => 5]);
```

### Dropdown

A styled select menu. **Remember to include `js/dropdowns.js` on the page.**

```php
$products = [
    "ms-80sh" => "MS-80SH",
    "ms-80"   => "MS-80",
    "ms-60"   => "MS-60",
];

new Dropdown("Product of interest", name: "product", options: $products, required: true);
new Dropdown("Product of interest", name: "product", options: $products, value: "ms-80");
```

Takes the same arguments as `Input` (without `type:` and with a different `placeholder:`), plus:

| Argument       | Default    | Description |
|----------------|------------|-------------|
| `options:`     | `[]`       | The choices as `value => visible text`. |
| `value:`       | `null`     | The value that starts selected. |
| `placeholder:` | `"Select"` | The text shown before anything is chosen. Pass `null` to leave it out. |

The dropdown is a real `<select>` underneath, so it submits, validates (`required`) and resets like any other form field.
To react to changes in JavaScript, listen for the usual `change` event on the select.

### Product card

Shows a product with an optional image, label, description, specification table and call to action.
Only the title is required, and each part you leave out is not shown.

```php
new ProductCard(
    title: "MS-80SH",
    label: "Class A",
    description: "Class A pyranometer with ventilator and heater for utility-scale PV",
    image: "/assets/images/ms80sh.png",
    table: [
        "ISO 9060:2018"    => "spectrally flat, Class A",
        "IEC 61724-1:2021" => "Class A system compliant",
        "Warranty"         => "5 years",
    ],
    cta: "View product",
    ctaUrl: "/products/ms-80sh"
);
```

| Argument       | Default    | Description |
|----------------|------------|-------------|
| `title:`       | (required) | The product name. |
| `label:`       | `null`     | A small tag above the title, e.g. `"Class A"` or `"New"`. |
| `description:` | `null`     | A short text below the title. |
| `image:`       | `null`     | Image URL. Without it, the card has no image area. |
| `imageAlt:`    | `title`    | Alt text for the image. |
| `table:`       | `[]`       | Specification rows, see below. |
| `cta:`         | `null`     | Call-to-action text at the bottom of the card. |
| `ctaUrl:`      | `null`     | Where the call to action links to. Without a URL, the call to action is plain text. |

**Writing the table:** write one `"name" => "value"` pair per row, in the order you want them shown.
A PHP array can't hold the same name twice, so write a row with a repeated name, or with more than two columns, as a list:

```php
table: [
    "Warranty" => "5 years",
    ["Warranty", "10 years (extended)"],   // same name as the row above
]
```

A product list from a database could look like this:

```php
<div style="display: flex; gap: 24px; flex-wrap: wrap;">
    <?php foreach ($products as $product) {
        new ProductCard(
            title: $product['name'],
            description: $product['summary'],
            image: $product['image_url'],
            table: $product['specs'],   // e.g. ["Warranty" => "5 years", ...]
            cta: "View product",
            ctaUrl: "/products/" . urlencode($product['slug'])
        );
    } ?>
</div>
```

### Contact card

Shows a person or department with an optional photo, contact details and button.
Only the first line is required, and each part you leave out is not shown.
For example, without `email:` the email row and its icon are left out.
The email, phone and website are clickable links (`mailto:`, `tel:` and `https://`). Write them the way you want them shown; the link target is worked out for you. The website opens in a new tab.

```php
new ContactCard(
    first: "Jane Doe",
    second: "EKO Instruments B.V.",
    third: "Sales Engineer",
    email: "jane.doe@example.com",
    phone: "+31 70 123 4567",
    website: "www.eko-instruments.com",
    image: "/assets/images/jane.jpg",
    button: "Contact Jane",
    buttonIcon: "/assets/icons/envelope-outline.svg"
);
```

| Argument            | Default    | Description |
|---------------------|------------|-------------|
| `first:`            | (required) | The first line, in bold. Usually the name. |
| `second:`           | `null`     | The second line, e.g. the company. |
| `third:`            | `null`     | The third line, e.g. the job title. |
| `email:`            | `null`     | Email address, shown with an envelope icon. |
| `phone:`            | `null`     | Phone number, shown with a phone icon. |
| `website:`          | `null`     | Website, shown with a globe icon, e.g. `"www.eko-instruments.com"`. |
| `image:`            | `null`     | Photo URL, shown left of the details. |
| `imageAlt:`         | `first`    | Alt text for the photo. |
| `button:`           | `null`     | Label of the outlined button at the bottom. Without it, there is no button. |
| `buttonIcon:`       | `null`     | Icon URL shown before the button label. |
| `buttonAttributes:` | `[]`       | Extra attributes to hook the button up to your page, e.g. `["id" => "contact-jane"]` or `["onclick" => "openContactForm('jane')"]`. `class` and `type` can't be changed. |

The three button options:

```php
new ContactCard("Jane Doe");                                         // no button
new ContactCard("Jane Doe", button: "Contact Jane");                 // label only
new ContactCard("Jane Doe", button: "Contact Jane",
                buttonIcon: "/assets/icons/envelope-outline.svg");   // label and icon
```

---

## Icons

The design system includes about 1,600 SVG icons in `assets/icons/`, in `-outline`, `-solid` and `-monochrome` variants.
Icons take on the text colour around them. To place one directly in your HTML:

```html
<span class="icon" style="--icon-url: url('/assets/icons/call-outline.svg')" aria-hidden="true"></span>
```

Change the size with `--icon-width` and `--icon-height`, e.g. `style="--icon-url: url(...); --icon-width: 24px; --icon-height: 24px"`.

## Full example

```php
<?php
require 'php/button.php';
require 'php/fields.php';

use Fields\Input;
use Fields\Textarea;
use Fields\Dropdown;

$emailError = $_SERVER['REQUEST_METHOD'] === 'POST'
    && !filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact us</title>
    <link rel="stylesheet" href="/css/main.css">
    <script src="/js/dropdowns.js" defer></script>
</head>
<body>
    <form method="post">
        <?php
        new Input("Name", name: "name", required: true);

        new Input(
            "Email address",
            name: "email",
            type: "email",
            value: $_POST['email'] ?? null,
            required: true,
            state: $emailError ? "error" : null,
            info: $emailError ? "Please enter a valid email address." : null
        );

        new Dropdown("Product of interest", name: "product", options: [
            "ms-80sh" => "MS-80SH",
            "ms-80"   => "MS-80",
        ]);

        new Textarea("Message", name: "message");

        new Button("Send", type: "submit", icon: "/assets/icons/envelope-outline.svg");
        ?>
    </form>
</body>
</html>
```

## Customising the look

All colours, sizes and spacing come from CSS custom properties (tokens):

- `css/tokens/brand.css` holds the raw EKO brand values: colours, font and font sizes.
- `css/tokens/semantic.css` holds what each value is used for, including per-component tokens such as `--button-*`, `--field-*` and `--product-card-*`.

To adjust a component in your application, override its token in your own stylesheet after `main.css`, instead of editing the design system's files:

```css
:root {
    --product-card-width: 360px;
}
```

## Viewing every component

`index.php` renders every component in all of its states. Run it from the design system folder:

```
php -S localhost:8000
```

Then open <http://localhost:8000/index.php>.
