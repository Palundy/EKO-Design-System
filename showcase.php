<?php
/**
 * The EKO design system showcase: a magazine-style tour of every component and its states.
 * Every example on this page is rendered by the real PHP components, so it always shows the current design system.
 *
 * Serve it from the repository root (`php -S localhost:8000`, then open /showcase.php),
 * because the icon URLs are root-absolute.
 */
require 'php/button.php';
require 'php/fields.php';
require 'php/cards.php';
require 'php/spec-table.php';
require 'php/navigation.php';
require 'php/section-heading.php';

use Fields\Input;
use Fields\Textarea;
use Fields\Dropdown;
use Cards\ProductCard;
use Cards\ContactCard;
use Cards\ApplicationCard;
use Cards\IndustryCard;
use Cards\ArticleCard;
use Navigation\Breadcrumb;
use Navigation\NavItem;


/** The chapters of the magazine, in order: anchor => [title, summary]. Used for the contents and the top bar. */
const CHAPTERS = [
    'foundations' => ['Foundations', 'Colour, type and space: the tokens every component is built from.'],
    'buttons' => ['Buttons', 'Four colours, six states, one consistent way to act.'],
    'fields' => ['Form fields', 'Inputs, text areas and dropdowns that explain themselves.'],
    'links' => ['Links', 'Quiet inline links and bold calls to action.'],
    'icons' => ['Icons', 'Around 1,600 icons that take on the colour around them.'],
    'navigation' => ['Navigation', 'Menu items and breadcrumbs that show where you are.'],
    'section-heading' => ['Section heading', 'The title that opens every part of a page.'],
    'product-card' => ['Product card', 'An instrument at a glance, with its key specifications.'],
    'contact-card' => ['Contact card', 'A person behind the product, one click away.'],
    'image-cards' => ['Image cards', 'Applications, industries and stories, led by a photo.'],
    'spec-table' => ['Spec table', 'The full specifications, readable at a glance.'],
];



/**
 * Prints the head of a chapter: the large outlined number, the kicker, the title and the lede.
 *
 * @param string $anchor
 * The chapter's key in `CHAPTERS`, used as the element id.
 *
 * @param string $kicker
 * The small line above the title (e.g., "Components").
 *
 * @param string $lede
 * The introduction under the title. It is inserted as HTML, so it may contain `<code>`.
 */
function printChapterHead($anchor, $kicker, $lede) {
    $number = str_pad(array_search($anchor, array_keys(CHAPTERS)) + 1, 2, '0', STR_PAD_LEFT);
    $title = CHAPTERS[$anchor][0];
    echo "<div class=\"sc-chapter-head\">";
    echo "<div class=\"sc-chapter-number\" aria-hidden=\"true\">$number</div>";
    echo "<div><p class=\"sc-kicker\">" . escapeHtml($kicker) . " · Chapter $number</p>";
    echo "<h2 class=\"sc-chapter-title\">" . escapeHtml($title) . "</h2>";
    echo "<p class=\"sc-lede\">$lede</p></div>";
    echo "</div>";
}

/**
 * Prints a numbered figure: a stage with the live example, and a caption under it.
 *
 * @param string $number
 * The figure number (e.g., "2.1").
 *
 * @param string $caption
 * The caption text. It is inserted as HTML, so it may contain `<code>`.
 *
 * @param callable $render
 * A function that prints the example. Components echo themselves, so it simply creates them.
 *
 * @param string $stageClass [optional]
 * Extra classes for the stage (e.g., "sc-stage-flush" for an example that fills it).
 *
 * @param string $style [optional]
 * Extra inline styles for the stage (e.g., "background: var(--color-surface);").
 */
function printFigure($number, $caption, $render, $stageClass = '', $style = '') {
    echo "<figure class=\"sc-figure\">";
    echo "<div class=\"sc-stage $stageClass\" style=\"$style\">";
    $render();
    echo "</div>";
    echo "<figcaption><strong>Fig. $number</strong><span>$caption</span></figcaption>";
    echo "</figure>";
}

/**
 * Prints the state index: the sidebar that names every state or variant of a component.
 *
 * @param array $states
 * The states as name => explanation. The explanation is inserted as HTML, so it may contain `<code>`.
 *
 * @param string $title [optional]
 * The heading of the sidebar.
 */
function printStates($states, $title = 'States & variants') {
    echo "<aside class=\"sc-states\"><p class=\"sc-kicker sc-states-title\">" . escapeHtml($title) . "</p><dl>";
    foreach ($states as $name => $explanation) {
        echo "<dt>" . escapeHtml($name) . "</dt><dd>$explanation</dd>";
    }
    echo "</dl></aside>";
}

/**
 * Builds a footnote marker: a small superscript number that links to the footnote at the end of the chapter.
 *
 * @param string $id
 * The footnote id, matching a key passed to `printFootnotes()` (e.g., "buttons-events").
 *
 * @param int $number
 * The number shown, matching the footnote's position in its chapter.
 *
 * @return string
 * The marker's HTML.
 */
function footnoteRef($id, $number) {
    return "<sup class=\"sc-footnote-ref\"><a href=\"#fn-$id\" id=\"fnref-$id\" aria-label=\"Footnote $number\">$number</a></sup>";
}

/**
 * Prints the footnotes of a chapter, each with a link back to its marker.
 *
 * @param array $notes
 * The footnotes in order, as id => text. The id matches `footnoteRef()`.
 * The text is inserted as HTML, so it may contain `<code>`.
 */
function printFootnotes($notes) {
    echo "<aside class=\"sc-footnotes\" aria-label=\"Footnotes\"><ol>";
    foreach ($notes as $id => $text) {
        echo "<li id=\"fn-$id\">$text <a class=\"sc-footnote-back\" href=\"#fnref-$id\" aria-label=\"Back to the text\">↩</a></li>";
    }
    echo "</ol></aside>";
}

/**
 * Prints a block of example code.
 *
 * @param string $code
 * The code, shown exactly as written (it is escaped).
 */
function printCode($code) {
    echo "<pre class=\"sc-code\"><code>" . escapeHtml(trim($code)) . "</code></pre>";
}

/**
 * Escapes a value for use in HTML text or a quoted attribute.
 *
 * @param mixed $value
 * The value to escape. It is converted to a string first.
 *
 * @return string
 * The escaped value.
 */
function escapeHtml($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EKO Design System</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/showcase/showcase.css">
    <script src="/js/dropdowns.js" defer></script>
</head>
<body class="sc-page">

    <!-- Top bar -->
    <header class="sc-topbar">
        <div class="sc-wrap">
            <a class="sc-wordmark" href="#top"><strong>EKO</strong><span>Design System</span></a>
            <nav class="sc-topbar-links" aria-label="Chapters">
                <a class="nav-item" href="#foundations">Foundations</a>
                <a class="nav-item" href="#buttons">Components</a>
                <a class="nav-item" href="#product-card">Cards</a>
                <a class="nav-item" href="#spec-table">Tables</a>
            </nav>
            <?php new Button("Dark mode", state: "negative", icon: "/assets/icons/moon-outline.svg", attributes: ["id" => "sc-theme-toggle", "aria-pressed" => "false"]); ?>
        </div>
        <div class="sc-progress" aria-hidden="true"></div>
    </header>

    <main id="top">

        <!-- Cover -->
        <section class="sc-cover">
            <div class="sc-wrap">
                <p class="sc-kicker">EKO Instruments · Design System · <?= date('F Y') ?></p>
                <h1 class="sc-cover-title">Precision, built to scale.</h1>
                <p class="sc-cover-lede">
                    A tour of the components behind the EKO website: what they are for, how they behave,
                    and every state they can be in. Everything you see on this page is rendered live by the design system.
                </p>
                <ol class="sc-cover-lines">
                    <li><strong>11 chapters</strong>From foundations to full specification tables.</li>
                    <li><strong>3 token layers</strong>Brand, semantic and component: change once, update everywhere.</li>
                    <li><strong>Light &amp; dark</strong>Switch the theme in the top bar to see every page change.</li>
                    <li><strong>PHP &amp; HTML</strong>Each component comes with a class and plain markup.</li>
                </ol>
            </div>
        </section>
        <div class="sc-brand-rule"></div>

        <!-- Contents -->
        <section class="sc-contents" aria-labelledby="contents-title">
            <div class="sc-wrap">
                <div class="sc-contents-head">
                    <h2 id="contents-title">On this page</h2>
                    <p class="sc-kicker">11 chapters</p>
                </div>
                <ol class="sc-contents-list">
                    <?php $number = 1; foreach (CHAPTERS as $anchor => [$title, $summary]) { ?>
                        <li>
                            <a href="#<?= $anchor ?>">
                                <span class="sc-contents-number"><?= str_pad($number++, 2, '0', STR_PAD_LEFT) ?></span>
                                <span class="sc-contents-title"><?= escapeHtml($title) ?></span>
                                <span class="sc-contents-summary"><?= escapeHtml($summary) ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ol>
            </div>
        </section>


        <!-- 01 Foundations -->
        <section class="sc-chapter" id="foundations">
            <div class="sc-wrap">
                <?php printChapterHead('foundations', 'The basics',
                    'Every component is built from the same three layers of tokens: the EKO brand values, the role each value plays,
                    and the component that uses it. Change a role once and every component follows. That is exactly how dark mode works.'); ?>
                <div class="sc-chapter-body">
                    <div class="sc-stack">
                        <?php printFigure('1.1', 'Colour roles. Components never use a brand colour directly, only these roles. The values are read live from the current theme.', function () {
                            $swatches = [
                                ['--color-primary', 'Primary', 'Actions and CTAs'],
                                ['--color-text-brand', 'Text brand', 'Headings and labels'],
                                ['--color-text', 'Text', 'Body copy'],
                                ['--color-text-muted', 'Text muted', 'Help text'],
                                ['--color-accent', 'Accent', 'Tags and categories'],
                                ['--color-text-accent', 'Text accent', 'Navigation chevrons'],
                                ['--color-surface-raised', 'Surface raised', 'Cards'],
                                ['--color-surface-highlight', 'Surface highlight', 'Hovered options'],
                                ['--color-border', 'Border', 'Rules and fields'],
                                ['--color-link-hover', 'Link hover', 'Inline links'],
                                ['--color-error', 'Error', 'Validation'],
                                ['--color-success', 'Success', 'Validation'],
                                ['--color-enquiry', 'Enquiry', 'Add to list'],
                                ['--color-destructive', 'Destructive', 'Delete, remove'],
                            ];
                            echo "<div class=\"sc-swatches\">";
                            foreach ($swatches as [$token, $name, $role]) {
                                echo "<div class=\"sc-swatch\" style=\"--sc-swatch: var($token)\" data-token=\"$token\">"
                                    . "<strong>$name</strong><code>$token</code><span>$role</span><code class=\"sc-swatch-value\"></code></div>";
                            }
                            echo "</div>";
                        }); ?>

                        <?php printFigure('1.2', 'The type scale, set in Reader. Bold for headlines and actions, regular for reading. Body text runs at 140% line height, headings at 110%.', function () {
                            $styles = [
                                ['Display', '--font-size-4xlarge', '48px · 110% · bold', 'font-size: var(--font-size-4xlarge); font-weight: 700; line-height: 1.1; letter-spacing: -0.6px', 'Almost a century of precision.'],
                                ['Section heading', '--font-size-3xlarge', '38px · 110% · bold', 'font-size: var(--font-size-3xlarge); font-weight: 700; line-height: 1.1; letter-spacing: -0.6px', 'Popular Products'],
                                ['Lead', '--font-size-2xlarge', '30px · 110% · bold', 'font-size: var(--font-size-2xlarge); font-weight: 700; line-height: 1.1', 'ISO Certified'],
                                ['Card title', '--font-size-xlarge', '22px · 110% · bold', 'font-size: var(--font-size-xlarge); font-weight: 700; line-height: 1.1; letter-spacing: -0.6px', 'MS-80SH'],
                                ['Body large', '--font-size-large', '20px · 140%', 'font-size: var(--font-size-large)', 'Precision data for solar and thermal projects.'],
                                ['Body', '--font-size-default', '18px · 140%', 'font-size: var(--font-size-default)', 'Scientific instruments. Reliable measurements.'],
                                ['Body small', '--font-size-small', '15px · 140%', 'font-size: var(--font-size-small)', 'Class A pyranometer with ventilator and heater.'],
                                ['Metadata', '--font-size-xsmall', '12px · 140%', 'font-size: var(--font-size-xsmall)', '6 minutes read · 12 November 2026'],
                                ['Tags & tables', '--font-size-2xsmall', '10px · bold', 'font-size: var(--font-size-2xsmall); font-weight: 700; text-transform: uppercase', 'Class A · Instruments'],
                            ];
                            echo "<div class=\"sc-type\">";
                            foreach ($styles as [$name, $token, $spec, $style, $sample]) {
                                echo "<div class=\"sc-type-row\"><div class=\"sc-type-meta\"><strong>$name</strong>$token<br>$spec</div>"
                                    . "<div class=\"sc-type-sample\" style=\"$style\">$sample</div></div>";
                            }
                            echo "</div>";
                        }); ?>

                        <?php printFigure('1.3', 'The spacing scale. Gaps and paddings always come from this list, so layouts line up across components.', function () {
                            echo "<div class=\"sc-space\">";
                            foreach (['3xsmall' => 2, '2xsmall' => 4, 'xsmall' => 6, 'small' => 8, 'medium' => 12, 'large' => 16, 'xlarge' => 20, '2xlarge' => 24, '3xlarge' => 32, '4xlarge' => 64] as $name => $px) {
                                echo "<div class=\"sc-space-row\"><span class=\"sc-space-label\">--space-$name · {$px}px</span>"
                                    . "<span class=\"sc-space-bar\" style=\"--sc-space: calc(var(--space-$name) * 4)\"></span></div>";
                            }
                            echo "</div>";
                        }); ?>
                    </div>

                    <div class="sc-stack">
                        <blockquote class="sc-quote">
                            <p>“Solid fills remain flat; use the blue–cyan–teal spectrum only for the brand rule.”</p>
                            <cite>EKO Library 2027 · Foundations</cite>
                        </blockquote>
                        <?php printStates([
                            'Brand tokens' => 'The raw EKO values, such as <code>--brand-eko-blue</code>. Only the semantic layer uses them.',
                            'Semantic tokens' => 'What a value is for, such as <code>--color-text-brand</code>. Themes override only these.',
                            'Component tokens' => 'One per design decision in a component, such as <code>--product-card-width</code>. Override them to restyle one component.',
                            'Themes' => 'Dark mode is a single block of semantic overrides under <code>[data-theme="dark"]</code>. Try it with the switch in the top bar.',
                        ], 'How the tokens work'); ?>
                    </div>
                </div>
            </div>
        </section>


        <!-- 02 Buttons -->
        <section class="sc-chapter" id="buttons">
            <div class="sc-wrap">
                <?php printChapterHead('buttons', 'Components',
                    'Buttons start an action. Blue is the default, red removes, green confirms and yellow is reserved for enquiries
                    and adding to a list. Every colour has the same six states, and hover and pressed are mixed from the base colour,
                    so a new colour gets its states for free.'); ?>
                <div class="sc-chapter-body">
                    <div class="sc-stack">
                        <?php printFigure('2.1', 'Every variant in every state. Hover and pressed are shown frozen here; on a real page they follow the mouse.', function () {
                            $columns = ['Default' => '', 'Hover' => 'sc-force-hover', 'Pressed' => 'sc-force-pressed', 'Disabled' => '', 'Loading' => '', 'Negative' => ''];
                            echo "<div class=\"sc-matrix\" style=\"--sc-matrix-columns: 6\"><span></span>";
                            foreach (array_keys($columns) as $label) {
                                echo "<span class=\"sc-matrix-label\">$label</span>";
                            }
                            foreach ([null => 'Primary', 'destructive' => 'Destructive', 'positive' => 'Positive', 'enquiry' => 'Enquiry'] as $style => $name) {
                                echo "<span class=\"sc-matrix-label\">$name</span>";
                                foreach ($columns as $label => $class) {
                                    $state = in_array($label, ['Disabled', 'Loading', 'Negative']) ? strtolower($label) : null;
                                    echo "<div class=\"$class\">";
                                    new Button("Label", style: $style ?: null, state: $state);
                                    echo "</div>";
                                }
                            }
                            echo "</div>";
                        }); ?>

                        <?php printFigure('2.2', 'With an icon. The icon follows the text colour, also when the button is outlined or hovered.', function () {
                            new Button("Send enquiry", style: "enquiry", icon: "/assets/icons/envelope-outline.svg");
                            new Button("Download datasheet", icon: "/assets/icons/download-outline.svg");
                            new Button("Contact sales", state: "negative", icon: "/assets/icons/call-outline.svg");
                        }); ?>

                        <?php printCode('
new Button("Save");
new Button("Delete", style: "destructive", state: "loading");
new Button("Send enquiry", style: "enquiry", icon: "/assets/icons/envelope-outline.svg");
                        '); ?>
                    </div>
                    <?php printStates([
                        'Default' => 'The resting state, filled with the variant colour.',
                        'Hover' => 'Darkens by 15% (<code>--state-hover-overlay</code>).',
                        'Pressed' => 'Darkens by 30% while the mouse button is down.',
                        'Disabled' => 'Muted grey and not clickable. Uses the native <code>disabled</code> attribute.',
                        'Loading' => 'A spinner replaces the icon; the button keeps its colour. Stops spinning for visitors who prefer reduced motion.',
                        'Negative' => 'Outlined, for secondary actions and links to other pages. Fills with its colour on hover.',
                        'Events' => 'Attach click handlers through the <code>attributes</code> argument.' . footnoteRef('buttons-events', 1),
                    ]); ?>
                </div>
                <?php printFootnotes([
                    'buttons-events' => 'Event listeners can be injected by setting them as an attribute of the element, through the
                        <code>attributes</code> argument: <code>new Button("Send", attributes: ["onclick" =&gt; "sendForm()"]);</code>
                        The value is escaped, so quotes in it are safe. For anything longer than a single call, give the button an
                        <code>id</code> and attach the listener in your own script with <code>addEventListener</code>.',
                ]); ?>
            </div>
        </section>


        <!-- 03 Form fields -->
        <section class="sc-chapter" id="fields">
            <div class="sc-wrap">
                <?php printChapterHead('fields', 'Components',
                    'A field is a label, a control and a line of help text underneath. The help text is linked to the control,
                    so screen readers read it out too. Text inputs, text areas and dropdowns share the same states.'); ?>
                <div class="sc-chapter-body">
                    <div class="sc-stack">
                        <?php printFigure('3.1', 'Text input in its five states. Focus is frozen here to show the blue border.', function () {
                            new Input("Full name", name: "sc-name", placeholder: "John Doe", info: "Your complete name.", required: true);
                            echo "<div class=\"sc-force-focus\">";
                            new Input("Full name", name: "sc-name-focus", value: "John Doe", info: "Focus: a blue border.", required: true);
                            echo "</div>";
                            new Input("Email address", name: "sc-email-error", type: "email", value: "john@", state: "error", info: "Please enter a valid email address.", required: true);
                            new Input("Email address", name: "sc-email-success", type: "email", value: "john.doe@example.com", state: "success", info: "Looks good!", required: true);
                            new Input("Full name", name: "sc-name-disabled", value: "John Doe", state: "disabled", info: "This field is disabled.");
                        }); ?>

                        <?php printFigure('3.2', 'Dropdown and text area. The dropdown is a native select underneath, so it submits, validates and resets like any field. Open it to see the option states.', function () {
                            $options = ['ms-80sh' => 'MS-80SH Pyranometer', 'ms-80' => 'MS-80 Pyranometer', 'ms-60' => 'MS-60 Pyranometer', 'ms-40' => 'MS-40 Pyranometer'];
                            new Dropdown("Product of interest", id: "sc-product", options: $options, info: "Choose an instrument.", required: true);
                            new Dropdown("Product of interest", id: "sc-product-selected", options: $options, value: "ms-80sh", state: "success", info: "Successfully selected!");
                            new Textarea("Message", name: "sc-message", placeholder: "How can we help?", info: "We usually answer within one working day.");
                        }); ?>

                        <?php printCode('
new Input("Email address", name: "email", type: "email", required: true,
          state: "error", info: "Please enter a valid email address.");

new Dropdown("Product of interest", name: "product", options: $products, value: "ms-80");
                        '); ?>
                    </div>
                    <?php printStates([
                        'Default' => 'Light grey border. The label gets an asterisk when the field is required.',
                        'Focus' => 'Blue border while typing. The dropdown gets a thicker border when it is open.',
                        'Error' => 'Red border and red help text. Use the help text to explain how to fix it.',
                        'Success' => 'Green border and green help text, after a successful check.',
                        'Disabled' => 'Grey background, greyed label. The value is not submitted.',
                        'Dropdown options' => 'Hover and keyboard focus get a light blue background; the selected option is bold and blue.',
                        'Events' => 'React to typing or a new choice through the <code>attributes</code> argument.' . footnoteRef('fields-events', 1),
                    ]); ?>
                </div>
                <?php printFootnotes([
                    'fields-events' => 'Event listeners can be injected by setting them as an attribute of the element. The
                        <code>attributes</code> argument of <code>Input</code>, <code>Textarea</code> and <code>Dropdown</code> lands on the
                        control itself: <code>new Input("Email", name: "email", attributes: ["oninput" =&gt; "checkEmail(this)"]);</code>
                        For a dropdown, listen to <code>onchange</code> on the native select: the styled menu passes every choice on to it
                        as a normal <code>input</code> and <code>change</code> event.',
                ]); ?>
            </div>
        </section>


        <!-- 04 Links -->
        <section class="sc-chapter" id="links">
            <div class="sc-wrap">
                <?php printChapterHead('links', 'Components',
                    'Two kinds of links. The inline link sits quietly inside running text. The action link is a bold call to action
                    that ends a card or a section, always with an arrow.'); ?>
                <div class="sc-chapter-body">
                    <div class="sc-stack">
                        <?php printFigure('4.1', 'Inline links in their default, hover and disabled states.', function () {
                            echo "<p style=\"margin: 0; max-width: 60ch; line-height: 1.6\">Read the <a class=\"link\" href=\"#links\">calibration guide</a> before you start. "
                                . "On hover, <span class=\"sc-force-hover\"><a class=\"link\" href=\"#links\">the link turns soft blue</a></span>, "
                                . "and a <a class=\"link\" aria-disabled=\"true\">disabled link</a> is greyed out and cannot be clicked.</p>";
                        }); ?>

                        <?php printFigure('4.2', 'Action links. On hover they stay blue and get an underline.', function () {
                            echo "<a class=\"link link-action\" href=\"#links\">Learn More <span aria-hidden=\"true\">→</span></a>";
                            echo "<span class=\"sc-force-hover\"><a class=\"link link-action\" href=\"#links\">Learn More <span aria-hidden=\"true\">→</span></a></span>";
                        }); ?>

                        <?php printCode('
<a class="link" href="/guide">calibration guide</a>
<a class="link link-action" href="/products">View all <span aria-hidden="true">→</span></a>
                        '); ?>
                    </div>
                    <?php printStates([
                        'Inline' => 'Inherits the size of the text around it. No underline; turns soft blue on hover.',
                        'Action' => '20px bold. Stays blue and underlines on hover.',
                        'Disabled' => 'Set <code>aria-disabled="true"</code>: links have no native disabled state.',
                        'Focus' => 'A thin outline when reached with the keyboard.',
                    ]); ?>
                </div>
            </div>
        </section>


        <!-- 05 Icons -->
        <section class="sc-chapter" id="icons">
            <div class="sc-wrap">
                <?php printChapterHead('icons', 'Components',
                    'Around 1,600 Unicons in outline, solid and monochrome styles. An icon is a mask over the text colour, so it automatically matches
                    the button, link or heading it sits in. Set <code>--icon-width</code> and <code>--icon-height</code> to resize it.'); ?>
                <div class="sc-chapter-body">
                    <div class="sc-stack">
                        <?php printFigure('5.1', 'Outline icons for interface elements, solid icons for emphasis and small sizes.', function () {
                            $sets = [
                                'Outline' => ['sun-outline', 'temperature-outline', 'cloud-outline', 'bolt-outline', 'setting-outline', 'download-outline'],
                                'Solid' => ['chart-solid', 'chart-pie-solid', 'star-solid', 'lock-solid', 'clock-solid', 'check-circle-solid'],
                            ];
                            echo "<div class=\"sc-icon-grid\">";
                            foreach ($sets as $name => $icons) {
                                echo "<span class=\"sc-matrix-label\">$name</span>";
                                foreach ($icons as $icon) {
                                    echo "<span class=\"icon\" style=\"--icon-url: url('/assets/icons/$icon.svg')\" title=\"$icon\" aria-hidden=\"true\"></span>";
                                }
                            }
                            echo "</div>";
                        }); ?>

                        <?php printFigure('5.2', 'One icon, many colours: each one simply takes the colour of its text.', function () {
                            foreach (['var(--color-primary)', 'var(--color-accent)', 'var(--color-text-accent)', 'var(--color-error)', 'var(--color-success)', 'var(--color-text-muted)'] as $colour) {
                                echo "<span class=\"icon\" style=\"--icon-url: url('/assets/icons/sun-outline.svg'); --icon-width: 40px; --icon-height: 40px; color: $colour\" aria-hidden=\"true\"></span>";
                            }
                        }); ?>

                        <?php printCode('
<span class="icon" style="--icon-url: url(\'/assets/icons/sun-outline.svg\')" aria-hidden="true"></span>
                        '); ?>
                    </div>
                    <?php printStates([
                        'Outline' => 'File names ending in <code>-outline</code>. The default for buttons and links.',
                        'Solid' => 'File names ending in <code>-solid</code>. For small sizes and emphasis.',
                        'Monochrome' => 'File names ending in <code>-monochrome</code>. These are two-tone, and many have a filled background shape that shows as a solid block. Check one before using it, or pick the outline or solid version.',
                        'Size' => 'Defaults to 20px. Set <code>--icon-width</code> and <code>--icon-height</code> on the icon or a parent.',
                    ], 'Styles'); ?>
                </div>
            </div>
        </section>


        <!-- 06 Navigation -->
        <section class="sc-chapter" id="navigation">
            <div class="sc-wrap">
                <?php printChapterHead('navigation', 'Components',
                    'Menu items lead to the main sections; the breadcrumb shows the way back. Both use a small yellow chevron,
                    the one place where the warning yellow appears outside of enquiries.'); ?>
                <div class="sc-chapter-body">
                    <div class="sc-stack">
                        <?php printFigure('6.1', 'Navigation items. The section the visitor is in is bold with a blue line underneath.', function () {
                            new NavItem("Products", "#navigation");
                            new NavItem("Industries", "#navigation", active: true);
                            new NavItem("Calibration", "#navigation");
                            new NavItem("About EKO", "#navigation");
                        }); ?>

                        <?php printFigure('6.2', 'The breadcrumb spans the full page width, with the page gutter on the left. The current page is bold and not a link.', function () {
                            echo "<div style=\"width: 100%\">";
                            new Breadcrumb(["Home" => "#navigation", "Pyranometers" => "#navigation", "MS-80SH" => "#navigation", "Talk to an Expert"]);
                            echo "</div>";
                        }, 'sc-stage-flush'); ?>

                        <?php printCode('
new Navigation\NavItem("Industries", "/industries", active: true);

new Navigation\Breadcrumb([
    "Home" => "/", "Pyranometers" => "/pyranometers", "Talk to an Expert",
]);
                        '); ?>
                    </div>
                    <?php printStates([
                        'Navigation item' => 'Regular blue text with a chevron.',
                        'Active' => 'Bold, with a 2px blue line under the text and chevron. Set with <code>aria-current</code>.',
                        'Breadcrumb link' => 'Underlines on hover.',
                        'Current page' => 'The last item, bold and not clickable.',
                    ]); ?>
                </div>
            </div>
        </section>


        <!-- 07 Section heading -->
        <section class="sc-chapter" id="section-heading">
            <div class="sc-wrap">
                <?php printChapterHead('section-heading', 'Components',
                    'Every section of a page opens with the same heading: 38px bold, with an optional action link on the right
                    that leads to the full overview.'); ?>
                <div class="sc-chapter-body">
                    <div class="sc-stack">
                        <?php printFigure('7.1', 'With an action link, and on its own.', function () {
                            echo "<div style=\"display: flex; flex-direction: column; gap: 48px; width: 100%\">";
                            new SectionHeading("Popular Products", link: "View all", linkUrl: "#section-heading");
                            new SectionHeading("About EKO");
                            echo "</div>";
                        }); ?>
                        <?php printCode('new SectionHeading("Popular Products", link: "View all", linkUrl: "/products");'); ?>
                    </div>
                    <?php printStates([
                        'Title only' => 'For sections without an overview page.',
                        'With link' => 'The link sits on the baseline of the title and wraps below it on small screens.',
                        'Heading level' => 'Choose <code>headingLevel</code> 1–6 to fit the page outline; the look stays the same.',
                    ], 'Variants'); ?>
                </div>
            </div>
        </section>


        <!-- 08 Product card -->
        <section class="sc-chapter" id="product-card">
            <div class="sc-wrap">
                <?php printChapterHead('product-card', 'Cards',
                    'The product card shows an instrument with its class, a one-line pitch and the specifications that matter most.
                    Every part except the title is optional, and left out completely when it is not given.'); ?>
                <div class="sc-chapter-body">
                    <div class="sc-stack">
                        <?php printFigure('8.1', 'Complete, and stripped back to title, label, description and link.', function () {
                            new ProductCard(
                                title: "MS-80SH",
                                label: "Class A",
                                description: "Class A pyranometer with ventilator and heater for utility-scale PV",
                                image: "/assets/images/ms80sh.png",
                                imageAlt: "MS-80SH Pyranometer",
                                table: [
                                    "ISO 9060:2018" => "spectrally flat, Class A",
                                    "IEC 61724-1:2021" => "Class A system compliant",
                                    "Warranty" => "5 years",
                                    "Recalibration" => "5 years",
                                ],
                                cta: "View product",
                                ctaUrl: "#product-card"
                            );
                            new ProductCard(
                                title: "ICF-02",
                                label: "New!",
                                description: "From ISO standards and uncertainty budgets to fully automated indoor calibration, everything your lab needs to know, in one place.",
                                image: "assets/images/icf-02.png",
                                imageAlt: "EKO Instruments ICF-02 - Indoor Calibration Facility",
                                table: [
                                    ["Standard", "IS0 9847:2023"],
                                    ["Light Source", "300 LED or 150W Metal Halide"]
                                ],
                                cta: "Request Quote"
                            );
                            new ProductCard(
                                title: "Product Card",
                                description: "This is a card without an image, without a table and without a label.",
                                cta: "Call to action"
                            );
                        }, 'sc-stage-raised', 'background:var(--color-surface);'); ?>
                        <?php printCode('
new ProductCard(
    title: "MS-80SH", label: "Class A", image: "/assets/images/ms80sh.png",
    table: ["Warranty" => "5 years", "Recalibration" => "5 years"],
    cta: "View product", ctaUrl: "/products/ms-80sh"
);
                        '); ?>
                    </div>
                    <?php printStates([
                        'Image' => 'Optional. Product photos are shown at 67% width, centred.',
                        'Label' => 'A short tag such as the ISO class or "New!", in teal.',
                        'Specifications' => 'Name and value rows. Repeat a name by writing the row as a list.',
                        'Call to action' => 'A link when a URL is given; it darkens on hover.',
                    ], 'Parts'); ?>
                </div>
            </div>
        </section>


        <!-- 09 Contact card -->
        <section class="sc-chapter" id="contact-card">
            <div class="sc-wrap">
                <?php printChapterHead('contact-card', 'Cards',
                    'Behind every instrument is an expert. The contact card puts a face, a role and three ways to get in touch
                    on one card, with the email, phone and website as working links.'); ?>
                <div class="sc-chapter-body">
                    <div class="sc-stack">
                        <?php printFigure('9.1', 'With a photo and a button, and with the minimum: a name and one way to reach out.', function () {
                            new ContactCard(
                                first: "Jane Doe",
                                second: "EKO Instruments B.V.",
                                third: "Sales Engineer",
                                email: "jane.doe@example.com",
                                phone: "+31 (0)70 123 4567",
                                website: "www.eko-instruments.com",
                                image: "/assets/images/user-silhouette-02.svg",
                                button: "Contact Jane",
                                buttonIcon: "/assets/icons/envelope-outline.svg"
                            );
                            new ContactCard(
                                first: "Customer service",
                                third: "Questions about your order",
                                email: "service@example.com"
                            );
                        }, 'sc-stage-raised', 'background:var(--color-surface);'); ?>
                        <?php printCode('
new ContactCard(
    first: "Jane Doe", second: "EKO Instruments B.V.", third: "Sales Engineer",
    email: "jane.doe@example.com", phone: "+31 (0)70 123 4567",
    button: "Contact Jane", buttonIcon: "/assets/icons/envelope-outline.svg"
);
                        '); ?>
                    </div>
                    <?php printStates([
                        'Details' => 'Only the first line is required. Each of the three lines and each contact row appears only when given.',
                        'Contact links' => 'Email, phone and website open the mail app, the phone or a new tab.',
                        'Button' => 'None, a label, or a label with an icon.' . footnoteRef('contact-events', 1),
                        'Photo' => 'Optional, shown to the left of the details.',
                    ], 'Parts'); ?>
                </div>
                <?php printFootnotes([
                    'contact-events' => 'Event listeners can be injected by setting them as an attribute of the element. The button takes
                        them through <code>buttonAttributes</code>, for example to open a contact form for this person:
                        <code>buttonAttributes: ["onclick" =&gt; "openContactForm(\'jane\')"]</code>. Its <code>class</code> and
                        <code>type</code> cannot be changed this way.',
                ]); ?>
            </div>
        </section>


        <!-- 10 Image cards -->
        <section class="sc-chapter" id="image-cards">
            <div class="sc-wrap">
                <?php printChapterHead('image-cards', 'Cards',
                    'Three cards that lead with a photo: applications, industries and stories. They share one layout and one set
                    of tokens; only their size, type and extras differ. Without a photo, a calm grey placeholder holds the space.'); ?>
                <div class="sc-chapter-body sc-full">
                    <div class="sc-stack">
                        <?php printFigure('10.1', 'Application card and article card. The article title and description stop after three lines, and the share buttons open LinkedIn, X, Facebook or email.', function () {
                            echo "<div class=\"sc-photo\" style=\"--sc-photo-icon: url('/assets/icons/sun-outline.svg')\">";
                            new ApplicationCard(
                                title: "Solar Energy",
                                description: "Resource assessment, performance monitoring and plant optimization.",
                                cta: "Learn More",
                                ctaUrl: "#image-cards"
                            );
                            echo "</div>";
                            new ArticleCard(
                                title: "How to choose the right pyranometer for a utility-scale PV plant",
                                url: "https://www.eko-instruments.com/",
                                tag: "Instruments",
                                description: "A short introduction to the ISO 9060 classes, what they mean for your yield assessment and when a heated, ventilated instrument pays off.",
                                readTime: "6 minutes read",
                                share: true
                            );
                            echo "<div class=\"sc-photo\" style=\"--sc-photo-icon: url('/assets/icons/temperature-outline.svg')\">";
                            new ApplicationCard(
                                title: "Meteorology",
                                description: "Accurate radiation data for weather and climate networks.",
                                cta: "Learn More",
                                ctaUrl: "#image-cards"
                            );
                            echo "</div>";
                        }, 'sc-stage-raised', 'background:var(--color-surface);'); ?>

                        <?php printFigure('10.2', 'Industry card. The whole card is a link; hover it to see the title underline.', function () {
                            echo "<div class=\"sc-photo\" style=\"--sc-photo-icon: url('/assets/icons/bolt-outline.svg')\">";
                            new IndustryCard(
                                title: "Solar Energy",
                                description: "Precision instruments for solar resource assessment, PV performance monitoring, and weather station integration.",
                                url: "#image-cards"
                            );
                            echo "</div>";
                        }, 'sc-stage-raised', 'background:var(--color-surface);'); ?>

                        <?php printCode('
new ApplicationCard(title: "Solar Energy", description: "…", cta: "Learn More", ctaUrl: "/applications/solar");
new IndustryCard(title: "Solar Energy", description: "…", url: "/industries/solar");
new ArticleCard(title: "…", url: "https://…", tag: "Instruments", readTime: "6 minutes read", share: true);
                        '); ?>
                    </div>
                </div>
            </div>
        </section>


        <!-- 11 Spec table -->
        <section class="sc-chapter" id="spec-table">
            <div class="sc-wrap">
                <?php printChapterHead('spec-table', 'Tables',
                    'The full specifications of an instrument, in EKO blue lines. One value per row gives a simple key/value table;
                    several values turn it into a comparison, for example against the ISO class limits.'); ?>
                <div class="sc-chapter-body sc-full">
                    <div class="sc-stack">
                        <?php printFigure('11.1', 'A comparison table with a header row: the ISO 9060 Class A limits next to the MS-80SH.', function () {
                            echo "<div style=\"width: 100%\">";
                            new SpecTable(
                                title: "ISO Specifications",
                                header: ["ISO 9060:2018 Parameters", "CLASS A", "MS-80SH"],
                                rows: [
                                    "Response time 95%" => ["< 10s", "< 0.5s"],
                                    "Zero offset A, Thermal Radiation (200W/m²)" => ["± 7W/m²", "± 1W/m²"],
                                    "Zero offset B, Temperature change (5K/hr)" => ["± 2W/m²", "± 1W/m²"],
                                    "Non-stability (change/year)" => ["± 0.8%", "< 0.5% / 5 years"],
                                    "Non-linearity (100 to 1000W/m²)" => ["± 0.5%", "± 0.2%"],
                                    "Spectral Error" => ["± 0.5%", "± 0.2%"],
                                ]
                            );
                            echo "</div>";
                        }); ?>

                        <?php printFigure('11.2', 'A key/value table without a header row.', function () {
                            echo "<div style=\"width: 100%\">";
                            new SpecTable(
                                title: "Specifications",
                                rows: [
                                    "Wavelength Range (nm)" => "285 to 3000",
                                    "Signal Output" => "Modbus 485 RTU / SDI-12",
                                    "Operating temperature" => "-40 to 80°C",
                                    "Ingress Protection" => "IP 67",
                                ]
                            );
                            echo "</div>";
                        }); ?>

                        <?php printCode('
new SpecTable(
    title: "ISO Specifications",
    header: ["ISO 9060:2018 Parameters", "CLASS A", "MS-80SH"],
    rows: ["Response time 95%" => ["< 10s", "< 0.5s"]]
);
                        '); ?>
                    </div>
                </div>
            </div>
        </section>
    </main>


    <!-- Colophon -->
    <footer class="sc-colophon">
        <div class="sc-wrap">
            <div>
                <h2>Using the system</h2>
                <p>Link <code>css/main.css</code>, require the PHP components you need, and create them where they should appear. The README explains every argument.</p>
            </div>
            <div>
                <h2>Changing the look</h2>
                <p>Override a component token to restyle one component, or semantic tokens to restyle everything. Your own CSS always wins over the design system.</p>
            </div>
            <div>
                <h2>Colophon</h2>
                <p>Set in Reader. Colours from the EKO 2024 palette. Designs from the EKO Library 2027. Every example on these pages is rendered live.</p>
            </div>
        </div>
    </footer>

    <script>
        // Theme switch: toggles the draft dark theme and remembers the choice in this browser
        (function () {
            const root = document.documentElement;
            const toggle = document.getElementById('sc-theme-toggle');

            function applyTheme(theme) {
                if (theme === 'dark') {
                    root.dataset.theme = 'dark';
                } else {
                    delete root.dataset.theme;
                }
                toggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
                toggle.lastChild.textContent = theme === 'dark' ? ' Light mode' : ' Dark mode';
                showSwatchValues();
            }

            // Show the current hex value under every colour swatch
            function showSwatchValues() {
                document.querySelectorAll('.sc-swatch').forEach(function (swatch) {
                    const value = getComputedStyle(swatch).getPropertyValue('--sc-swatch');
                    const probe = document.createElement('span');
                    probe.style.color = value;
                    document.body.appendChild(probe);
                    const rgb = getComputedStyle(probe).color.match(/\d+/g) || [];
                    probe.remove();
                    swatch.querySelector('.sc-swatch-value').textContent =
                        '#' + rgb.slice(0, 3).map(function (n) { return Number(n).toString(16).padStart(2, '0'); }).join('');
                });
            }

            let saved = null;
            try { saved = localStorage.getItem('eko-showcase-theme'); } catch (e) {}
            applyTheme(saved === 'dark' ? 'dark' : 'light');

            toggle.addEventListener('click', function () {
                const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
                try { localStorage.setItem('eko-showcase-theme', next); } catch (e) {}
                applyTheme(next);
            });

            // Reading progress in the top bar
            function updateProgress() {
                const max = document.documentElement.scrollHeight - window.innerHeight;
                root.style.setProperty('--sc-progress', max > 0 ? window.scrollY / max : 0);
            }
            window.addEventListener('scroll', updateProgress, { passive: true });
            updateProgress();
        })();
    </script>
</body>
</html>
