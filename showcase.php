<?php
/**
 * The EKO design system showcase: a magazine-style tour of every component and its states.
 * Every example on this page is rendered by the real PHP components, so it always shows the current design system.
 *
 * Serve it from the repository root (`php -S localhost:8000`, then open /showcase.php),
 * because the icon URLs are root-absolute.
 */
require_once __DIR__ . '/vendor/autoload.php';

use EKO\DesignSystem\DesignSystem;
use EKO\DesignSystem\Button;
use EKO\DesignSystem\SectionHeading;
use EKO\DesignSystem\SpecTable;

use EKO\DesignSystem\Fields\Input;
use EKO\DesignSystem\Fields\Textarea;
use EKO\DesignSystem\Fields\Dropdown;

use EKO\DesignSystem\Cards\ProductCard;
use EKO\DesignSystem\Cards\ContactCard;
use EKO\DesignSystem\Cards\ApplicationCard;
use EKO\DesignSystem\Cards\IndustryCard;
use EKO\DesignSystem\Cards\ArticleCard;

use EKO\DesignSystem\Navigation\Breadcrumb;
use EKO\DesignSystem\Navigation\NavItem;

use EKO\DesignSystem\Addons\EkoData\EkoData;
use EKO\DesignSystem\Addons\EkoData\StatusTracker;
use EKO\DesignSystem\Addons\Icf02\Icf02;
use EKO\DesignSystem\Addons\Icf02\Pincode;
use EKO\DesignSystem\Addons\Icf02\Sidebar;


// The showcase shows every addon too, so their stylesheets are linked in the head
DesignSystem::enableAddon(EkoData::class);
DesignSystem::enableAddon(Icf02::class);


/** The EKO logo for the ICF-02 sidebar example. The addon ships no logo (apps pass their own URL), so the showcase inlines one. */
const SHOWCASE_LOGO = 'data:image/svg+xml,' . '%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 124.236 48"%3E%3Cg fill="%2300559e"%3E'
    . '%3Cpath d="M464.97 270.236a24 24 0 1 0 24.032 24 23.906 23.906 0 0 0-24.032-24zm.032 35.88a11.88 11.88 0 1 1 11.881-11.88A11.88 11.88 0 0 1 465 306.116z" transform="translate(-364.766 -270.236)"/%3E'
    . '%3Cpath d="M437.829 316.506h-6.213l-16.69-21.03-9.543 9.907v11.123h-4.957v-43.931h4.957v26.233l25.229-26.233h6.4l-18.688 19.384z" transform="translate(-359.441 -270.543)"/%3E'
    . '%3Cpath d="M387.157 277.1h-28.945v15h22.68v4.519h-22.68v15.376h28.976v4.518h-33.934v-43.93h33.9z" transform="translate(-353.254 -270.543)"/%3E'
    . '%3C/g%3E%3C/svg%3E';


/**
 * The themes the theme button cycles through, in order: `data-theme` value => label.
 * An empty value is the default (light) theme, which needs no attribute. Each other value needs a file in assets/css/themes/.
 */
const THEMES = [
    '' => 'Light',
    'dark' => 'Dark',
    'sky' => 'Sky',
];


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
    'eko-data' => ['EKO Data addon', 'Status trackers that follow a deployment step by step.'],
    'icf-02' => ['ICF-02 addon', 'The calibration facility\'s main menu and its pincode sign-in.'],
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
 * A function that prints the example, e.g. with `echo new Button("Label");`.
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
 * Given one code sample, it prints a single block with the language label in the upper left corner.
 * Given several (e.g., PHP and Jinja), it prints one block with a tab per language. Choosing a language
 * switches every block on the page to it, and the choice is remembered in this browser.
 *
 * @param string|array $code
 * The code, shown exactly as written (it is escaped), or the samples as language label => code.
 *
 * @param string $language [optional]
 * The language label of a single code sample. Defaults to "PHP". Ignored when `$code` is an array.
 */
function printCode($code, $language = 'PHP') {
    static $blockCount = 0;
    $samples = is_array($code) ? $code : [$language => $code];

    if (count($samples) === 1) {
        echo "<pre class=\"sc-code\" data-language=\"" . escapeHtml(array_key_first($samples)) . "\"><code>"
            . escapeHtml(trim(reset($samples))) . "</code></pre>";
        return;
    }

    // One tab and one panel per language; the first language is shown until the script restores a saved choice
    $blockCount++;
    $tabs = '';
    $panels = '';
    $first = true;
    foreach ($samples as $label => $sample) {
        $id = "sc-code-$blockCount-" . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $label));
        $tabs .= "<button type=\"button\" class=\"sc-code-tab\" role=\"tab\" id=\"$id-tab\" aria-controls=\"$id\""
            . " aria-selected=\"" . ($first ? 'true' : 'false') . "\" tabindex=\"" . ($first ? '0' : '-1') . "\""
            . " data-language=\"" . escapeHtml($label) . "\">" . escapeHtml($label) . "</button>";
        $panels .= "<pre class=\"sc-code\" role=\"tabpanel\" id=\"$id\" aria-labelledby=\"$id-tab\" data-language=\"" . escapeHtml($label) . "\""
            . ($first ? '' : ' hidden') . "><code>" . escapeHtml(trim($sample)) . "</code></pre>";
        $first = false;
    }
    echo "<div class=\"sc-code-tabs\"><div class=\"sc-code-tablist\" role=\"tablist\" aria-label=\"Language\">$tabs</div>$panels</div>";
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
    <?= DesignSystem::stylesheet() ?>
    <link rel="stylesheet" href="/showcase/showcase.css">
    <?= DesignSystem::scripts() ?>
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
                <a class="nav-item" href="#eko-data">Addons</a>
            </nav>
            <?php echo new Button("Theme: Light", state: "negative", icon: "color-palette-outline", attributes: ["id" => "sc-theme-toggle", "data-themes" => json_encode(THEMES), "title" => "Switch to the next theme"]); ?>
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
                    <li><strong><?= count(CHAPTERS) ?> chapters</strong>From foundations to full specification tables and application addons.</li>
                    <li><strong>3 token layers</strong>Brand, semantic and component: change once, update everywhere.</li>
                    <li><strong><?= count(THEMES) ?> themes</strong>Switch the theme in the top bar to see every page change.</li>
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
                    <p class="sc-kicker"><?= count(CHAPTERS) ?> chapters</p>
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
                            'Themes' => 'A theme is a single block of semantic overrides, such as <code>[data-theme="dark"]</code> or the experimental <code>[data-theme="sky"]</code>. Cycle through them with the theme button in the top bar.',
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
                                    echo new Button("Label", style: $style ?: null, state: $state);
                                    echo "</div>";
                                }
                            }
                            echo "</div>";
                        }); ?>

                        <?php printFigure('2.2', 'With an icon. The icon follows the text colour, also when the button is outlined or hovered.', function () {
                            echo new Button("Send enquiry", style: "enquiry", icon: "envelope-outline");
                            echo new Button("Download datasheet", icon: "download-outline");
                            echo new Button("Contact sales", state: "negative", icon: "call-outline");
                        }); ?>

                        <?php printCode([
                            'PHP' => '
// 2.1: style is "destructive", "positive" or "enquiry" (none for primary),
// state is "disabled", "loading" or "negative"
echo new Button("Label");
echo new Button("Label", style: "destructive", state: "loading");
echo new Button("Label", style: "positive", state: "negative");

// 2.2
echo new Button("Send enquiry", style: "enquiry", icon: "envelope-outline");
echo new Button("Download datasheet", icon: "download-outline");
echo new Button("Contact sales", state: "negative", icon: "call-outline");
                            ',
                            'Jinja' => '
{# 2.1: style is "destructive", "positive" or "enquiry" (none for primary),
   state is "disabled", "loading" or "negative" #}
{{ eko.Button("Label") }}
{{ eko.Button("Label", style = "destructive", state = "loading") }}
{{ eko.Button("Label", style = "positive", state = "negative") }}

{# 2.2 #}
{{ eko.Button("Send enquiry", style = "enquiry", icon = "envelope-outline") }}
{{ eko.Button("Download datasheet", icon = "download-outline") }}
{{ eko.Button("Contact sales", state = "negative", icon = "call-outline") }}
                            ',
                        ]); ?>
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
                        <code>attributes</code> argument: <code>echo new Button("Send", attributes: ["onclick" =&gt; "sendForm()"]);</code>
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
                            echo new Input("Full name", name: "sc-name", placeholder: "John Doe", info: "Your complete name.", required: true);
                            echo "<div class=\"sc-force-focus\">";
                            echo new Input("Full name", name: "sc-name-focus", value: "John Doe", info: "Focus: a blue border.", required: true);
                            echo "</div>";
                            echo new Input("Email address", name: "sc-email-error", type: "email", value: "john@", state: "error", info: "Please enter a valid email address.", required: true);
                            echo new Input("Email address", name: "sc-email-success", type: "email", value: "john.doe@example.com", state: "success", info: "Looks good!", required: true);
                            echo new Input("Full name", name: "sc-name-disabled", value: "John Doe", state: "disabled", info: "This field is disabled.");
                        }); ?>

                        <?php printFigure('3.2', 'Dropdown and text area. The dropdown is a native select underneath, so it submits, validates and resets like any field. Open it to see the option states.', function () {
                            $options = ['ms-80sh' => 'MS-80SH Pyranometer', 'ms-80' => 'MS-80 Pyranometer', 'ms-60' => 'MS-60 Pyranometer', 'ms-40' => 'MS-40 Pyranometer'];
                            echo new Dropdown("Product of interest", id: "sc-product", options: $options, info: "Choose an instrument.", required: true);
                            echo new Dropdown("Product of interest", id: "sc-product-selected", options: $options, value: "ms-80sh", state: "success", info: "Successfully selected!");
                            echo new Textarea("Message", name: "sc-message", placeholder: "How can we help?", info: "We usually answer within one working day.");
                        }); ?>

                        <?php printCode([
                            'PHP' => '
// 3.1
echo new Input("Full name", name: "name", placeholder: "John Doe", info: "Your complete name.", required: true);
echo new Input("Email address", name: "email", type: "email", value: "john@", required: true,
          state: "error", info: "Please enter a valid email address.");
echo new Input("Email address", name: "email", type: "email", value: "john.doe@example.com", required: true,
          state: "success", info: "Looks good!");
echo new Input("Full name", name: "name", value: "John Doe", state: "disabled", info: "This field is disabled.");

// 3.2
$products = ["ms-80sh" => "MS-80SH Pyranometer", "ms-80" => "MS-80 Pyranometer",
             "ms-60" => "MS-60 Pyranometer", "ms-40" => "MS-40 Pyranometer"];
echo new Dropdown("Product of interest", id: "product", options: $products, info: "Choose an instrument.", required: true);
echo new Dropdown("Product of interest", id: "product", options: $products, value: "ms-80sh",
             state: "success", info: "Successfully selected!");
echo new Textarea("Message", name: "message", placeholder: "How can we help?",
             info: "We usually answer within one working day.");
                            ',
                            'Jinja' => '
{# 3.1 #}
{{ eko.Input("Full name", name = "name", placeholder = "John Doe", info = "Your complete name.", required = True) }}
{{ eko.Input("Email address", name = "email", type = "email", value = "john@", required = True,
             state = "error", info = "Please enter a valid email address.") }}
{{ eko.Input("Email address", name = "email", type = "email", value = "john.doe@example.com", required = True,
             state = "success", info = "Looks good!") }}
{{ eko.Input("Full name", name = "name", value = "John Doe", state = "disabled", info = "This field is disabled.") }}

{# 3.2 #}
{% set products = {"ms-80sh": "MS-80SH Pyranometer", "ms-80": "MS-80 Pyranometer",
                   "ms-60": "MS-60 Pyranometer", "ms-40": "MS-40 Pyranometer"} %}
{{ eko.Dropdown("Product of interest", id = "product", options = products, info = "Choose an instrument.", required = True) }}
{{ eko.Dropdown("Product of interest", id = "product", options = products, value = "ms-80sh",
                state = "success", info = "Successfully selected!") }}
{{ eko.Textarea("Message", name = "message", placeholder = "How can we help?",
                info = "We usually answer within one working day.") }}
                            ',
                        ]); ?>
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
                        control itself: <code>echo new Input("Email", name: "email", attributes: ["oninput" =&gt; "checkEmail(this)"]);</code>
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
<!-- 4.1 -->
<a class="link" href="/guide">calibration guide</a>
<a class="link" aria-disabled="true">disabled link</a>

<!-- 4.2 -->
<a class="link link-action" href="/products">Learn More <span aria-hidden="true">→</span></a>
                        ', 'HTML'); ?>
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
<!-- 5.1 -->
<span class="icon" style="--icon-url: url(\'/assets/icons/sun-outline.svg\')" aria-hidden="true"></span>
<span class="icon" style="--icon-url: url(\'/assets/icons/chart-solid.svg\')" aria-hidden="true"></span>

<!-- 5.2: 40px, in the colour of the text -->
<span class="icon" style="--icon-url: url(\'/assets/icons/sun-outline.svg\');
      --icon-width: 40px; --icon-height: 40px; color: var(--color-error)" aria-hidden="true"></span>
                        ', 'HTML'); ?>
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
                            echo new NavItem("Products", "#navigation");
                            echo new NavItem("Industries", "#navigation", active: true);
                            echo new NavItem("Calibration", "#navigation");
                            echo new NavItem("About EKO", "#navigation");
                        }); ?>

                        <?php printFigure('6.2', 'The breadcrumb spans the full page width, with the page gutter on the left. The current page is bold and not a link.', function () {
                            echo "<div style=\"width: 100%\">";
                            echo new Breadcrumb(["Home" => "#navigation", "Pyranometers" => "#navigation", "MS-80SH" => "#navigation", "Talk to an Expert"]);
                            echo "</div>";
                        }, 'sc-stage-flush'); ?>

                        <?php printCode([
                            'PHP' => '
// 6.1
echo new NavItem("Products", "/products");
echo new NavItem("Industries", "/industries", active: true);
echo new NavItem("Calibration", "/calibration");
echo new NavItem("About EKO", "/about");

// 6.2
echo new Breadcrumb([
    "Home" => "/", "Pyranometers" => "/pyranometers", "MS-80SH" => "/products/ms-80sh",
    "Talk to an Expert",
]);
                            ',
                            'Jinja' => '
{# 6.1 #}
{{ eko.NavItem("Products", "/products") }}
{{ eko.NavItem("Industries", "/industries", active = True) }}
{{ eko.NavItem("Calibration", "/calibration") }}
{{ eko.NavItem("About EKO", "/about") }}

{# 6.2: the current page has no URL #}
{{ eko.Breadcrumb({
    "Home": "/", "Pyranometers": "/pyranometers", "MS-80SH": "/products/ms-80sh",
    "Talk to an Expert": None,
}) }}
                            ',
                        ]); ?>
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
                            echo new SectionHeading("Popular Products", link: "View all", linkUrl: "#section-heading");
                            echo new SectionHeading("About EKO");
                            echo "</div>";
                        }); ?>
                        <?php printCode([
                            'PHP' => '
echo new SectionHeading("Popular Products", link: "View all", linkUrl: "/products");
echo new SectionHeading("About EKO");
                            ',
                            'Jinja' => '
{{ eko.SectionHeading("Popular Products", link = "View all", link_url = "/products") }}
{{ eko.SectionHeading("About EKO") }}
                            ',
                        ]); ?>
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
                            echo new ProductCard(
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
                            echo new ProductCard(
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
                            echo new ProductCard(
                                title: "Product Card",
                                description: "This is a card without an image, without a table and without a label.",
                                cta: "Call to action"
                            );
                        }, 'sc-stage-raised', 'background:var(--color-surface);'); ?>
                        <?php printCode([
                            'PHP' => '
echo new ProductCard(
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
    ctaUrl: "/products/ms-80sh"
);

// Repeat a name by writing the rows as lists; without ctaUrl the call to action is not a link
echo new ProductCard(
    title: "ICF-02",
    label: "New!",
    description: "From ISO standards and uncertainty budgets to fully automated indoor calibration, everything your lab needs to know, in one place.",
    image: "/assets/images/icf-02.png",
    imageAlt: "EKO Instruments ICF-02 - Indoor Calibration Facility",
    table: [
        ["Standard", "IS0 9847:2023"],
        ["Light Source", "300 LED or 150W Metal Halide"],
    ],
    cta: "Request Quote"
);

echo new ProductCard(
    title: "Product Card",
    description: "This is a card without an image, without a table and without a label.",
    cta: "Call to action"
);
                            ',
                            'Jinja' => '
{{ eko.ProductCard(
    title = "MS-80SH",
    label = "Class A",
    description = "Class A pyranometer with ventilator and heater for utility-scale PV",
    image = "/assets/images/ms80sh.png",
    image_alt = "MS-80SH Pyranometer",
    table = {
        "ISO 9060:2018": "spectrally flat, Class A",
        "IEC 61724-1:2021": "Class A system compliant",
        "Warranty": "5 years",
        "Recalibration": "5 years",
    },
    cta = "View product",
    cta_url = "/products/ms-80sh"
) }}

{# Repeat a name by writing the rows as lists; without cta_url the call to action is not a link #}
{{ eko.ProductCard(
    title = "ICF-02",
    label = "New!",
    description = "From ISO standards and uncertainty budgets to fully automated indoor calibration, everything your lab needs to know, in one place.",
    image = "/assets/images/icf-02.png",
    image_alt = "EKO Instruments ICF-02 - Indoor Calibration Facility",
    table = [
        ["Standard", "IS0 9847:2023"],
        ["Light Source", "300 LED or 150W Metal Halide"],
    ],
    cta = "Request Quote"
) }}

{{ eko.ProductCard(
    title = "Product Card",
    description = "This is a card without an image, without a table and without a label.",
    cta = "Call to action"
) }}
                            ',
                        ]); ?>
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
                            echo new ContactCard(
                                first: "Jane Doe",
                                second: "EKO Instruments B.V.",
                                third: "Sales Engineer",
                                email: "jane.doe@example.com",
                                phone: "+31 (0)70 123 4567",
                                website: "www.eko-instruments.com",
                                image: "/assets/images/user-silhouette-02.svg",
                                button: "Contact Jane",
                                buttonIcon: "envelope-outline"
                            );
                            echo new ContactCard(
                                first: "Customer service",
                                third: "Questions about your order",
                                email: "service@example.com"
                            );
                        }, 'sc-stage-raised', 'background:var(--color-surface);'); ?>
                        <?php printCode([
                            'PHP' => '
echo new ContactCard(
    first: "Jane Doe",
    second: "EKO Instruments B.V.",
    third: "Sales Engineer",
    email: "jane.doe@example.com",
    phone: "+31 (0)70 123 4567",
    website: "www.eko-instruments.com",
    image: "/assets/images/user-silhouette-02.svg",
    button: "Contact Jane",
    buttonIcon: "envelope-outline"
);

echo new ContactCard(
    first: "Customer service",
    third: "Questions about your order",
    email: "service@example.com"
);
                            ',
                            'Jinja' => '
{{ eko.ContactCard(
    first = "Jane Doe",
    second = "EKO Instruments B.V.",
    third = "Sales Engineer",
    email = "jane.doe@example.com",
    phone = "+31 (0)70 123 4567",
    website = "www.eko-instruments.com",
    image = "/assets/images/user-silhouette-02.svg",
    button = "Contact Jane",
    button_icon = "envelope-outline"
) }}

{{ eko.ContactCard(
    first = "Customer service",
    third = "Questions about your order",
    email = "service@example.com"
) }}
                            ',
                        ]); ?>
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
                            echo new ApplicationCard(
                                title: "Solar Energy",
                                description: "Resource assessment, performance monitoring and plant optimization.",
                                cta: "Learn More",
                                ctaUrl: "#image-cards"
                            );
                            echo "</div>";
                            echo new ArticleCard(
                                title: "How to choose the right pyranometer for a utility-scale PV plant",
                                url: "https://www.eko-instruments.com/",
                                tag: "Instruments",
                                description: "A short introduction to the ISO 9060 classes, what they mean for your yield assessment and when a heated, ventilated instrument pays off.",
                                readTime: "6 minutes read",
                                share: true
                            );
                            echo "<div class=\"sc-photo\" style=\"--sc-photo-icon: url('/assets/icons/temperature-outline.svg')\">";
                            echo new ApplicationCard(
                                title: "Meteorology",
                                description: "Accurate radiation data for weather and climate networks.",
                                cta: "Learn More",
                                ctaUrl: "#image-cards"
                            );
                            echo "</div>";
                        }, 'sc-stage-raised', 'background:var(--color-surface);'); ?>

                        <?php printFigure('10.2', 'Industry card. The whole card is a link; hover it to see the title underline.', function () {
                            echo "<div class=\"sc-photo\" style=\"--sc-photo-icon: url('/assets/icons/bolt-outline.svg')\">";
                            echo new IndustryCard(
                                title: "Solar Energy",
                                description: "Precision instruments for solar resource assessment, PV performance monitoring, and weather station integration.",
                                url: "#image-cards"
                            );
                            echo "</div>";
                        }, 'sc-stage-raised', 'background:var(--color-surface);'); ?>

                        <?php printCode([
                            'PHP' => '
// 10.1
echo new ApplicationCard(
    title: "Solar Energy",
    description: "Resource assessment, performance monitoring and plant optimization.",
    cta: "Learn More",
    ctaUrl: "/applications/solar"
);

echo new ArticleCard(
    title: "How to choose the right pyranometer for a utility-scale PV plant",
    url: "https://www.eko-instruments.com/",
    tag: "Instruments",
    description: "A short introduction to the ISO 9060 classes, what they mean for your yield assessment and when a heated, ventilated instrument pays off.",
    readTime: "6 minutes read",
    share: true
);

// 10.2
echo new IndustryCard(
    title: "Solar Energy",
    description: "Precision instruments for solar resource assessment, PV performance monitoring, and weather station integration.",
    url: "/industries/solar"
);
                            ',
                            'Jinja' => '
{# 10.1 #}
{{ eko.ApplicationCard(
    title = "Solar Energy",
    description = "Resource assessment, performance monitoring and plant optimization.",
    cta = "Learn More",
    cta_url = "/applications/solar"
) }}

{{ eko.ArticleCard(
    title = "How to choose the right pyranometer for a utility-scale PV plant",
    url = "https://www.eko-instruments.com/",
    tag = "Instruments",
    description = "A short introduction to the ISO 9060 classes, what they mean for your yield assessment and when a heated, ventilated instrument pays off.",
    read_time = "6 minutes read",
    share = True
) }}

{# 10.2 #}
{{ eko.IndustryCard(
    title = "Solar Energy",
    description = "Precision instruments for solar resource assessment, PV performance monitoring, and weather station integration.",
    url = "/industries/solar"
) }}
                            ',
                        ]); ?>
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
                            echo new SpecTable(
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
                            echo new SpecTable(
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

                        <?php printCode([
                            'PHP' => '
// 11.1
echo new SpecTable(
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

// 11.2
echo new SpecTable(
    title: "Specifications",
    rows: [
        "Wavelength Range (nm)" => "285 to 3000",
        "Signal Output" => "Modbus 485 RTU / SDI-12",
        "Operating temperature" => "-40 to 80°C",
        "Ingress Protection" => "IP 67",
    ]
);
                            ',
                            'Jinja' => '
{# 11.1 #}
{{ eko.SpecTable(
    title = "ISO Specifications",
    header = ["ISO 9060:2018 Parameters", "CLASS A", "MS-80SH"],
    rows = {
        "Response time 95%": ["< 10s", "< 0.5s"],
        "Zero offset A, Thermal Radiation (200W/m²)": ["± 7W/m²", "± 1W/m²"],
        "Zero offset B, Temperature change (5K/hr)": ["± 2W/m²", "± 1W/m²"],
        "Non-stability (change/year)": ["± 0.8%", "< 0.5% / 5 years"],
        "Non-linearity (100 to 1000W/m²)": ["± 0.5%", "± 0.2%"],
        "Spectral Error": ["± 0.5%", "± 0.2%"],
    }
) }}

{# 11.2 #}
{{ eko.SpecTable(
    title = "Specifications",
    rows = {
        "Wavelength Range (nm)": "285 to 3000",
        "Signal Output": "Modbus 485 RTU / SDI-12",
        "Operating temperature": "-40 to 80°C",
        "Ingress Protection": "IP 67",
    }
) }}
                            ',
                        ]); ?>
                    </div>
                </div>
            </div>
        </section>


        <!-- 12 EKO Data addon -->
        <section class="sc-chapter" id="eko-data">
            <div class="sc-wrap">
                <?php printChapterHead('eko-data', 'Addons',
                    'Components that only one application needs live in an addon, next to the core. The EKO Data addon adds a status tracker:
                    the steps of a deployment, what is done, what is running and how far it has come. Switch it on with
                    <code>DesignSystem::enableAddon(EkoData::class)</code>; its classes and tokens start with <code>ekodata-</code>.'); ?>
                <div class="sc-chapter-body">
                    <div class="sc-stack">
                        <?php printFigure('12.1', 'A deployment in progress: three steps done, the image build running, two steps to go.', function () {
                            echo "<div style=\"width: 100%; max-width: 640px\">";
                            echo new StatusTracker(
                                title: "Deployment status",
                                description: "Track the deployment pipeline for this release.",
                                icon: "list-ul-solid",
                                action: new Button("Verify", state: "negative"),
                                steps: [
                                    ["label" => "Release published", "status" => "done", "detail" => "2026-11-27, 11:47:27"],
                                    ["label" => "Release registered on EKO Data", "status" => "done", "detail" => "2026-11-27, 11:47:37"],
                                    ["label" => "Workflow: Image build started", "status" => "done", "detail" => "2026-11-27, 11:47:32"],
                                    ["label" => "Workflow: Image build done", "status" => "active"],
                                    ["label" => "Workflow: Pushed image to Docker Hub"],
                                    ["label" => "Deployment done!"],
                                ]
                            );
                            echo "</div>";
                        }); ?>

                        <?php printFigure('12.2', 'A failed step turns its marker and the progress bar red, and its detail line explains why.', function () {
                            echo "<div style=\"width: 100%; max-width: 640px\">";
                            echo new StatusTracker(
                                title: "Deployment status",
                                icon: "list-ul-solid",
                                action: new Button("Retry", state: "negative"),
                                steps: [
                                    ["label" => "Release published", "status" => "done", "detail" => "2026-11-27, 11:47:27"],
                                    ["label" => "Workflow: Image build", "status" => "error", "detail" => "Build failed after 2 min 14 s"],
                                    ["label" => "Deployment done!"],
                                ]
                            );
                            echo "</div>";
                        }); ?>

                        <?php printCode([
                            'PHP' => '
use EKO\DesignSystem\DesignSystem;
use EKO\DesignSystem\Button;
use EKO\DesignSystem\Addons\EkoData\EkoData;
use EKO\DesignSystem\Addons\EkoData\StatusTracker;

DesignSystem::enableAddon(EkoData::class);   // once, before DesignSystem::stylesheet()

echo new StatusTracker(
    title: "Deployment status",
    description: "Track the deployment pipeline for this release.",
    icon: "list-ul-solid",
    action: new Button("Verify", state: "negative"),
    steps: [
        ["label" => "Release published", "status" => "done", "detail" => "2026-11-27, 11:47:27"],
        ["label" => "status" => "error", "detail" => "Build failed after 2 min 14 s"],
        ["label" => "Deployment done!"],
    ]
);
                            ',
                            'Jinja' => '
{# In the app: EkoDesignSystem(app, addons = [EkoData]) #}
{{ eko.eko_data.StatusTracker(
    title = "Deployment status",
    description = "Track the deployment pipeline for this release.",
    icon = "list-ul-solid",
    action = eko.Button("Verify", state = "negative"),
    steps = [
        {"label": "Release published", "status": "done", "detail": "2026-11-27, 11:47:27"},
        {"label": "Workflow: Image build done", "status": "error", "detail": "Build failed after 2 min 14 s"},
        {"label": "Deployment done!"},
    ]
) }}
                            ',
                        ]); ?>
                    </div>
                    <?php printStates([
                        'Done' => 'A filled marker. Done steps fill the progress bar.',
                        'In progress' => 'A spinning ring, and <code>aria-current="step"</code>. The spin stops when reduced motion is preferred.',
                        'Pending' => 'An empty ring. The default when a step has no status.',
                        'Failed' => 'A red marker with an exclamation mark; the progress bar turns red.',
                        'Screen readers' => 'Each label is read after its state ("Done: Release published"), and the bar reports "3 of 6 steps done".',
                    ], 'Step states'); ?>
                </div>
            </div>
        </section>


        <!-- 13 ICF-02 addon -->
        <section class="sc-chapter" id="icf-02">
            <div class="sc-wrap">
                <?php printChapterHead('icf-02', 'Addons',
                    'The ICF-02 addon holds the components of the indoor calibration facility\'s web app. Its sidebar is the main menu:
                    the logo, the menu in labelled groups with the current page highlighted, and the unit\'s details with a few icon actions.
                    The pincode card signs an operator in, with a keypad that also follows the keyboard and a padlock that opens.
                    Switch it on with <code>DesignSystem::enableAddon(Icf02::class)</code>; its classes and tokens start with <code>icf02-</code>.'); ?>
                <div class="sc-chapter-body">
                    <div class="sc-stack">
                        <?php printFigure('13.1', 'The ICF-02 menu with "Calibrate" as the current page. Here it is 720px tall; in the app it fills the height of the window.', function () {
                            echo new Sidebar(
                                logo: SHOWCASE_LOGO,
                                groups: [
                                    "Work" => [
                                        ["label" => "Home", "url" => "#icf-02", "icon" => "house-outline"],
                                        ["label" => "Calibrate", "url" => "#icf-02", "icon" => "target-outline", "active" => true],
                                        ["label" => "Calibration history", "url" => "#icf-02", "icon" => "history-outline"],
                                        ["label" => "Orders", "url" => "#icf-02", "icon" => "clipboard-outline"],
                                    ],
                                    "People" => [
                                        ["label" => "Contacts", "url" => "#icf-02", "icon" => "users-outline"],
                                        ["label" => "Operators", "url" => "#icf-02", "icon" => "add-account-outline"],
                                    ],
                                    "Sensors" => [
                                        ["label" => "Test sensors", "url" => "#icf-02", "icon" => "chip-outline"],
                                        ["label" => "Reference sensors", "url" => "#icf-02", "icon" => "circle-layer-outline"],
                                        ["label" => "Sensor models", "url" => "#icf-02", "icon" => "list-outline"],
                                    ],
                                    "System" => [
                                        ["label" => "Settings", "url" => "#icf-02", "icon" => "setting-outline"],
                                        ["label" => "Documentation", "url" => "#icf-02", "icon" => "book-outline"],
                                    ],
                                ],
                                url: "#icf-02",
                                footerTitle: "Unit 005",
                                footerDetail: "v5.6.0",
                                footerStatus: "Developer Mode",
                                actions: [
                                    ["label" => "Send feedback", "icon" => "feedback-outline"],
                                    ["label" => "Shut down", "icon" => "power-button-outline", "url" => "#icf-02"],
                                ],
                                attributes: ["style" => "--icf02-sidebar-height: 720px"]
                            );
                        }); ?>

                        <?php printCode([
                            'PHP' => '
use EKO\DesignSystem\DesignSystem;
use EKO\DesignSystem\Addons\Icf02\Icf02;
use EKO\DesignSystem\Addons\Icf02\Sidebar;

DesignSystem::enableAddon(Icf02::class);   // once, before DesignSystem::stylesheet()

echo new Sidebar(
    logo: "/static/images/eko-logo.svg",
    groups: [
        "Work" => [
            ["label" => "Home", "url" => "/", "icon" => "house-outline"],
            ["label" => "Calibrate", "url" => "/calibrate", "icon" => "target-outline", "active" => true],
        ],
        "System" => [
            ["label" => "Settings", "url" => "/settings", "icon" => "setting-outline"],
        ],
    ],
    footerTitle: "Unit 005",
    footerDetail: "v5.6.0",
    footerStatus: "Developer Mode",
    actions: [
        ["label" => "Send feedback", "icon" => "feedback-outline", "attributes" => ["onclick" => "showFeedbackElement()"]],
        ["label" => "Shut down", "icon" => "power-button-outline", "url" => "/power"],
    ]
);
                            ',
                            'Jinja' => '
{# In the app: EkoDesignSystem(app, addons = [Icf02]) #}
{{ eko.icf_02.Sidebar(
    logo = url_for("static", filename = "images/eko-logo.svg"),
    groups = {
        "Work": [
            {"label": "Home", "url": "/", "icon": "house-outline"},
            {"label": "Calibrate", "url": "/calibrate", "icon": "target-outline", "active": True},
        ],
        "System": [
            {"label": "Settings", "url": "/settings", "icon": "setting-outline"},
        ],
    },
    footer_title = "Unit 005",
    footer_detail = "v5.6.0",
    footer_status = "Developer Mode",
    actions = [
        {"label": "Send feedback", "icon": "feedback-outline", "attributes": {"onclick": "showFeedbackElement()"}},
        {"label": "Shut down", "icon": "power-button-outline", "url": "/power"},
    ]
) }}
                            ',
                        ]); ?>

                        <?php printFigure('13.2', 'The pincode card. Try it: click the keys or type on your keyboard, then press Enter. <code>1234</code> unlocks it; any other pincode is rejected.', function () {
                            echo new Pincode(
                                title: "ICF-02 · Unit 005",
                                logo: SHOWCASE_LOGO,
                                link: "Change pincode",
                                attributes: ["id" => "sc-pincode-demo"]
                            );
                        }, 'sc-stage-raised'); ?>

                        <?php printFigure('13.3', 'The two answers: a wrong pincode shakes and turns red, a right one opens the shackle and turns green.', function () {
                            echo new Pincode(title: "ICF-02 · Unit 005", state: "error");
                            echo new Pincode(title: "ICF-02 · Unit 005", state: "unlocked", unlockedMessage: "Welcome back");
                        }, 'sc-stage-raised'); ?>

                        <?php printCode([
                            'PHP' => '
use EKO\DesignSystem\Addons\Icf02\Pincode;

// DesignSystem::scripts() loads pincode.js once the ICF-02 addon is enabled
echo new Pincode(
    title: "ICF-02 · Unit 005",
    logo: "/static/images/eko-logo.svg",
    link: "Change pincode"
);
                            ',
                            'Jinja' => '
{{ eko.icf_02.Pincode(
    title = "ICF-02 · Unit 005",
    logo = url_for("static", filename = "images/eko-logo.svg"),
    link = "Change pincode"
) }}
                            ',
                            'JavaScript' => '
// The app checks the pincode and answers with unlock() or reject()
document.addEventListener("icf02-pincode-submit", async (event) => {
    const response = await fetch("/api/system/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ pincode: event.detail.pincode }),
    });
    const result = await response.json();
    result.succes ? event.detail.unlock() : event.detail.reject();
});

// Fired when the unlock animation has finished
document.addEventListener("icf02-pincode-unlocked", () => location.reload());
                            ',
                        ]); ?>
                    </div>
                    <?php printStates([
                        'Current page' => 'The item with <code>"active" => true</code> gets <code>aria-current="page"</code>: a highlighted background, bold text and a bar on its left edge.',
                        'Hover' => 'Links and footer actions darken slightly, with the same overlay as the buttons.',
                        'Focus' => 'A visible outline on the logo link, every menu link and every action when reached with the keyboard.',
                        'Groups' => 'A group label is shown above its links and names the list for screen readers. A group with a numeric key has no label.',
                        'Footer' => 'Optional. The unit\'s details on the left, icon actions on the right; an action is a link with a <code>url</code>, otherwise a button.',
                        'System state' => 'An optional last footer line (<code>footerStatus</code>), such as "Developer Mode" or "New Update Available", set off as a small bold label on a light tint.',
                        'Layout' => 'The sidebar sticks to the top and is as tall as the window (<code>--icf02-sidebar-height</code>). Put it in the first column of the page.',
                        'Pincode: pressed' => 'A key sinks in while the mouse holds it, or while its number key on the keyboard is held down.',
                        'Pincode: checking' => 'After Enter, the dots pulse and the keys pause until the app calls <code>unlock()</code> or <code>reject()</code>.',
                        'Pincode: error' => 'The lock and dots shake and turn red, and the message says the pincode was wrong. The next key starts over.',
                        'Pincode: unlocked' => 'The shackle lifts and swings open, a ring pulses out, and the lock, dots and message turn green.',
                        'Reduced motion' => 'No shaking, swinging or pulsing; the colours and the message still show every state.',
                    ]); ?>
                </div>
            </div>
        </section>
    </main>


    <!-- Colophon -->
    <footer class="sc-colophon">
        <div class="sc-wrap">
            <div>
                <h2>Using the system</h2>
                <p>In PHP, require Composer's <code>vendor/autoload.php</code> and import the components you need, tell <code>DesignSystem::setBaseUrl()</code> where the design system's files are served from, and print <code>DesignSystem::stylesheet()</code> and <code>DesignSystem::scripts()</code> in the page head. In Flask, call <code>EkoDesignSystem(app)</code> once and use <code>{{ eko.stylesheet() }}</code>, <code>{{ eko.scripts() }}</code> and <code>{{ eko.Button(...) }}</code> in your templates. Then create the components where they should appear, passing icons by name (e.g., <code>"envelope-outline"</code>). The README explains every argument.</p>
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
        // Theme switch: cycles through the THEMES list from PHP and remembers the choice in this browser
        (function () {
            const root = document.documentElement;
            const toggle = document.getElementById('sc-theme-toggle');
            const themes = JSON.parse(toggle.dataset.themes);
            const themeKeys = Object.keys(themes);

            function applyTheme(theme) {
                if (!(theme in themes)) {
                    theme = '';
                }
                if (theme) {
                    root.dataset.theme = theme;
                } else {
                    delete root.dataset.theme;
                }
                toggle.lastChild.textContent = ' Theme: ' + themes[theme];
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
            applyTheme(saved || '');

            toggle.addEventListener('click', function () {
                const current = themeKeys.indexOf(root.dataset.theme || '');
                const next = themeKeys[(current + 1) % themeKeys.length];
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

            // ICF-02 pincode demo (figure 13.2): 1234 unlocks it, any other pincode is rejected, as a server would answer.
            // Once unlocked, the card locks again after a moment so it can be tried again
            document.addEventListener('icf02-pincode-submit', function (event) {
                setTimeout(function () {
                    if (event.detail.pincode === '1234') {
                        event.detail.unlock();
                    } else {
                        event.detail.reject();
                    }
                }, 600);
            });
            document.addEventListener('icf02-pincode-unlocked', function (event) {
                if (event.target.id === 'sc-pincode-demo') {
                    setTimeout(function () { event.target.icf02Pincode.reset(); }, 2000);
                }
            });

            // Code language tabs: choosing a language shows it in every code block and remembers it in this browser
            function showLanguage(language) {
                document.querySelectorAll('.sc-code-tabs').forEach(function (block) {
                    const tabs = block.querySelectorAll('.sc-code-tab');
                    const match = Array.prototype.some.call(tabs, function (tab) { return tab.dataset.language === language; });
                    if (!match) {
                        return;
                    }
                    tabs.forEach(function (tab) {
                        const selected = tab.dataset.language === language;
                        tab.setAttribute('aria-selected', selected ? 'true' : 'false');
                        tab.tabIndex = selected ? 0 : -1;
                        document.getElementById(tab.getAttribute('aria-controls')).hidden = !selected;
                    });
                });
            }

            document.querySelectorAll('.sc-code-tablist').forEach(function (tablist) {
                const tabs = Array.prototype.slice.call(tablist.querySelectorAll('.sc-code-tab'));

                tablist.addEventListener('click', function (event) {
                    const tab = event.target.closest('.sc-code-tab');
                    if (!tab) {
                        return;
                    }
                    try { localStorage.setItem('eko-showcase-language', tab.dataset.language); } catch (e) {}
                    // Keep the clicked block in place while the blocks above it change height
                    const top = tab.getBoundingClientRect().top;
                    showLanguage(tab.dataset.language);
                    window.scrollBy(0, tab.getBoundingClientRect().top - top);
                });

                // Arrow keys move between the tabs, as in any tab list
                tablist.addEventListener('keydown', function (event) {
                    const step = { ArrowRight: 1, ArrowLeft: -1 }[event.key];
                    if (!step) {
                        return;
                    }
                    event.preventDefault();
                    const next = tabs[(tabs.indexOf(document.activeElement) + step + tabs.length) % tabs.length];
                    next.focus();
                    next.click();
                });
            });

            let savedLanguage = null;
            try { savedLanguage = localStorage.getItem('eko-showcase-language'); } catch (e) {}
            if (savedLanguage) {
                showLanguage(savedLanguage);
            }
        })();
    </script>
</body>
</html>
