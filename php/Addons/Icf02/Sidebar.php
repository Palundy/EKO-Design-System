<?php
namespace EKO\DesignSystem\Addons\Icf02;

use EKO\DesignSystem\Component;
use EKO\DesignSystem\DesignSystem;
use InvalidArgumentException;


/**
 * `Sidebar` renders the ICF-02 application's main menu: a full-height column with the logo and product name,
 * the menu links in labelled groups, and an optional footer with the unit's details and icon actions
 * (`.icf02-sidebar`, see assets/addons/icf-02/css/components/sidebar.css). Part of the ICF-02 addon,
 * so enable it first with `DesignSystem::enableAddon(Icf02::class)`.
 * It returns its HTML instead of printing it: print it with `<?= new Sidebar(...) ?>` or `echo`,
 * or keep the string with `(string) new Sidebar(...)`.
 * Import it with `use EKO\DesignSystem\Addons\Icf02\Sidebar;` after requiring Composer's `vendor/autoload.php`.
 *
 * Example:
 * ```php
 * echo new Sidebar(
 *     logo: "/static/images/eko-logo.svg",
 *     groups: [
 *         "Work" => [
 *             ["label" => "Home", "url" => "/", "icon" => "house-outline", "active" => true],
 *             ["label" => "Calibrate", "url" => "/calibrate", "icon" => "target-outline"],
 *         ],
 *         "System" => [
 *             ["label" => "Settings", "url" => "/settings", "icon" => "setting-outline"],
 *         ],
 *     ],
 *     footerTitle: "Unit 005",
 *     footerDetail: "v5.6.0",
 *     footerStatus: "Developer Mode",
 *     actions: [
 *         ["label" => "Send feedback", "icon" => "feedback-outline", "attributes" => ["onclick" => "showFeedbackElement()"]],
 *         ["label" => "Shut down", "icon" => "power-button-outline", "url" => "/power"],
 *     ]
 * );
 * ```
 *
 * The page layout places the sidebar: it sticks to the top of the viewport and is as tall as it.
 * All text and attribute values are HTML-escaped.
 */
class Sidebar extends Component {


    /** The keys a menu item accepts. */
    private const VALID_ITEM_KEYS = [
        'label',
        'url',
        'icon',
        'active',
    ];

    /** The keys a footer action accepts. */
    private const VALID_ACTION_KEYS = [
        'label',
        'icon',
        'url',
        'attributes',
    ];


    /**
     * Builds the sidebar HTML, stores it in `$html`.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param array $groups
     * The menu, as group label => list of items. A group with a numeric key (a plain list entry) gets no label.
     * Each item is an array with the keys:
     * - "label" (required): the link text (e.g., "Calibrate").
     * - "url" (required): where the link goes (e.g., "/calibrate").
     * - "icon" [optional]: a bundled icon's name (e.g., "target-outline") or the URL of your own SVG icon.
     *   See `DesignSystem::resolveIcon()`.
     * - "active" [optional]: `true` for the link of the current page, which gets `aria-current="page"`.
     *
     * @param string $logo
     * The URL of the logo image at the top (e.g., "/static/images/eko-logo.svg"). It is used as given.
     *
     * @param string $title [optional]
     * The product name next to the logo, "ICF-02" by default.
     *
     * @param string $logoAlt [optional]
     * The logo's alternative text, "EKO" by default.
     *
     * @param string $url [optional]
     * Where the logo and product name link to, "/" by default.
     *
     * @param string|null $footerTitle [optional]
     * The first, bold line of the footer (e.g., "Unit 005").
     *
     * @param string|null $footerDetail [optional]
     * The second, small line of the footer (e.g., "v5.6.0").
     *
     * @param string|null $footerStatus [optional]
     * The last line of the footer: a state of the system, shown with a little emphasis
     * (e.g., "Developer Mode", "Team Mode", "Outdated", "New Update Available").
     *
     * @param array $actions [optional]
     * Icon buttons on the right of the footer, each an array with the keys:
     * - "label" (required): what the action does (e.g., "Shut down"), read by screen readers and shown as a tooltip.
     * - "icon" (required): a bundled icon's name or the URL of your own SVG icon.
     * - "url" [optional]: makes the action a link; without it, the action is a `<button type="button">`.
     * - "attributes" [optional]: extra HTML attributes as name => value (e.g., `["onclick" => "showFeedbackElement()"]`).
     *
     * @param string $label [optional]
     * The accessible name of the menu's `<nav>`, "Main" by default.
     *
     * @param array $attributes [optional]
     * Extra HTML attributes for the `<aside>` as name => value (e.g., `["id" => "sidebar"]`).
     * A "class" is added to the component's own class. `true` writes a bare attribute, `false` or `null` leaves it out.
     *
     * @throws InvalidArgumentException if `$groups` is empty, a group has no items, an item has no label or url
     * or an unknown key, `$logo` or `$title` is empty, an action has no label or icon or an unknown key,
     * or an icon names an unknown icon.
     */
    public function __construct(
        $groups,
        $logo,
        $title = 'ICF-02',
        $logoAlt = 'EKO',
        $url = '/',
        $footerTitle = null,
        $footerDetail = null,
        $footerStatus = null,
        $actions = [],
        $label = 'Main',
        $attributes = []
    ) {
        // Validate the arguments before building anything
        if (!is_array($groups) || count($groups) === 0) {
            throw new InvalidArgumentException("A sidebar needs at least one group");
        }
        if ($logo === null || $logo === '') {
            throw new InvalidArgumentException("A sidebar needs a logo");
        }
        if ($title === null || $title === '') {
            throw new InvalidArgumentException("A sidebar needs a title");
        }
        if (!is_array($actions)) {
            throw new InvalidArgumentException("The sidebar actions must be a list");
        }


        // Collect the aside's attributes, adding any extra class to the component's own
        $allAttributes = ['class' => 'icf02-sidebar'];
        foreach ($attributes as $key => $value) {
            if ($key === 'class') {
                $allAttributes['class'] .= ' ' . $value;
            } else {
                $allAttributes[$key] = $value;
            }
        }

        // Build the brand: the logo and the product name, linking to the home page
        $html = "<aside" . self::attributesToHtml($allAttributes) . ">";
        $html .= "<a class=\"icf02-sidebar-brand\" href=\"" . self::escape($url) . "\">";
        $html .= "<img class=\"icf02-sidebar-logo\" src=\"" . self::escape($logo) . "\" alt=\"" . self::escape($logoAlt) . "\">";
        $html .= "<span class=\"icf02-sidebar-title\">" . self::escape($title) . "</span>";
        $html .= "</a>";

        // Build the menu, one group at a time
        $html .= "<nav class=\"icf02-sidebar-nav\" aria-label=\"" . self::escape($label) . "\">";
        foreach ($groups as $groupLabel => $items) {
            $html .= self::renderGroup(is_int($groupLabel) ? null : $groupLabel, $items);
        }
        $html .= "</nav>";

        // Build the footer, only when it has something to show
        $hasMeta = ($footerTitle !== null && $footerTitle !== '') || ($footerDetail !== null && $footerDetail !== '');
        $hasStatus = $footerStatus !== null && $footerStatus !== '';
        if ($hasMeta || count($actions) > 0 || $hasStatus) {
            $html .= "<div class=\"icf02-sidebar-footer\">";
            if ($hasMeta) {
                $html .= "<div class=\"icf02-sidebar-meta\">";
                if ($footerTitle !== null && $footerTitle !== '') {
                    $html .= "<span class=\"icf02-sidebar-meta-title\">" . self::escape($footerTitle) . "</span>";
                }
                if ($footerDetail !== null && $footerDetail !== '') {
                    $html .= "<span class=\"icf02-sidebar-meta-detail\">" . self::escape($footerDetail) . "</span>";
                }
                $html .= "</div>";
            }
            if (count($actions) > 0) {
                $html .= "<div class=\"icf02-sidebar-actions\">";
                foreach ($actions as $action) {
                    $html .= self::renderAction($action);
                }
                $html .= "</div>";
            }
            // The system state goes last: it gets a full-width row, so a long state fits on one line
            if ($hasStatus) {
                $html .= "<span class=\"icf02-sidebar-status\">" . self::escape($footerStatus) . "</span>";
            }
            $html .= "</div>";
        }
        $html .= "</aside>";


        // Store the HTML
        $this->html = $html;
    }



    /**
     * Builds the HTML of one group: its optional label and its list of links.
     * The label is shown, but hidden from screen readers, because it also names the list (`aria-label`).
     *
     * @param string|null $groupLabel
     * The group's label, or `null` (or "") for a group without one.
     *
     * @param mixed $items
     * The group's items, as described for the `$groups` argument of the constructor.
     *
     * @return string
     * The group's `<div>`.
     *
     * @throws InvalidArgumentException if the group has no items or one of its items is invalid.
     */
    private static function renderGroup($groupLabel, $items) {
        if (!is_array($items) || count($items) === 0) {
            throw new InvalidArgumentException("Every sidebar group needs at least one item");
        }
        $hasLabel = $groupLabel !== null && $groupLabel !== '';

        $html = "<div class=\"icf02-sidebar-group\">";
        if ($hasLabel) {
            $html .= "<span class=\"icf02-sidebar-group-label\" aria-hidden=\"true\">" . self::escape($groupLabel) . "</span>";
        }
        $html .= "<ul class=\"icf02-sidebar-list\"" . ($hasLabel ? " aria-label=\"" . self::escape($groupLabel) . "\"" : '') . ">";
        foreach ($items as $item) {
            $html .= self::renderItem($item);
        }
        $html .= "</ul>";
        $html .= "</div>";
        return $html;
    }


    /**
     * Builds the HTML of one menu item: a link with an optional icon, marked as the current page when it is active.
     *
     * @param mixed $item
     * The item, as described for the `$groups` argument of the constructor.
     *
     * @return string
     * The item's `<li>`.
     *
     * @throws InvalidArgumentException if the item is not an array, has no label or url, has an unknown key
     * or names an unknown icon.
     */
    private static function renderItem($item) {
        // Validate the item
        $itemLabel = is_array($item) ? ($item['label'] ?? null) : null;
        if ($itemLabel === null || $itemLabel === '') {
            throw new InvalidArgumentException("Every sidebar item needs a label");
        }
        $itemUrl = $item['url'] ?? null;
        if ($itemUrl === null || $itemUrl === '') {
            throw new InvalidArgumentException("Every sidebar item needs a url");
        }
        foreach (array_keys($item) as $key) {
            if (!in_array($key, self::VALID_ITEM_KEYS, true)) {
                throw new InvalidArgumentException("Unknown sidebar item key: $key");
            }
        }
        $current = ($item['active'] ?? false) === true ? " aria-current=\"page\"" : '';


        $html = "<li><a class=\"icf02-sidebar-link\" href=\"" . self::escape($itemUrl) . "\"$current>";
        $html .= self::renderIcon($item['icon'] ?? null);
        $html .= self::escape($itemLabel);
        $html .= "</a></li>";
        return $html;
    }


    /**
     * Builds the HTML of one footer action: an icon-only link or button, named by its label.
     *
     * @param mixed $action
     * The action, as described for the `$actions` argument of the constructor.
     *
     * @return string
     * The action's `<a>` or `<button>`.
     *
     * @throws InvalidArgumentException if the action is not an array, has no label or icon, has an unknown key
     * or names an unknown icon.
     */
    private static function renderAction($action) {
        // Validate the action
        $actionLabel = is_array($action) ? ($action['label'] ?? null) : null;
        $actionIcon = is_array($action) ? ($action['icon'] ?? null) : null;
        if ($actionLabel === null || $actionLabel === '' || $actionIcon === null || $actionIcon === '') {
            throw new InvalidArgumentException("Every sidebar action needs a label and an icon");
        }
        foreach (array_keys($action) as $key) {
            if (!in_array($key, self::VALID_ACTION_KEYS, true)) {
                throw new InvalidArgumentException("Unknown sidebar action key: $key");
            }
        }
        $actionUrl = $action['url'] ?? null;
        $isLink = $actionUrl !== null && $actionUrl !== '';


        // The label names the icon-only control for screen readers and shows as a tooltip
        $allAttributes = ['class' => 'icf02-sidebar-action'];
        if ($isLink) {
            $allAttributes['href'] = $actionUrl;
        } else {
            $allAttributes['type'] = 'button';
        }
        $allAttributes['aria-label'] = $actionLabel;
        $allAttributes['title'] = $actionLabel;
        foreach ($action['attributes'] ?? [] as $key => $value) {
            if ($key === 'class') {
                $allAttributes['class'] .= ' ' . $value;
            } else {
                $allAttributes[$key] = $value;
            }
        }

        $tag = $isLink ? 'a' : 'button';
        return "<$tag" . self::attributesToHtml($allAttributes) . ">" . self::renderIcon($actionIcon) . "</$tag>";
    }


    /**
     * Builds the HTML of an icon, hidden from screen readers because the text next to it (or the label) names it.
     *
     * @param string|null $icon
     * A bundled icon's name or the URL of your own SVG icon. `null` or "" gives no icon.
     *
     * @return string
     * The icon's `<span>`, or "" without an icon.
     *
     * @throws InvalidArgumentException if `$icon` names an unknown icon.
     */
    private static function renderIcon($icon) {
        if ($icon === null || $icon === '') {
            return '';
        }
        $iconStyle = "--icon-url: url('" . self::escapeCssString(DesignSystem::resolveIcon($icon)) . "')";
        return "<span class=\"icon\" style=\"" . self::escape($iconStyle) . "\" aria-hidden=\"true\"></span>";
    }


    /**
     * Turns name => value pairs into an escaped attribute string with a leading space.
     *
     * @param array $attributes
     * The attributes as name => value. `true` writes a bare attribute (e.g., ` hidden`), `false` and `null` are skipped.
     *
     * @return string
     * The attributes as HTML (e.g., ` class="icf02-sidebar" id="sidebar"`).
     */
    private static function attributesToHtml($attributes) {
        $html = '';
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            $html .= ' ' . self::escape($key);
            if ($value !== true) {
                $html .= '="' . self::escape($value) . '"';
            }
        }
        return $html;
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
    private static function escape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }


    /**
     * Escapes a value for use inside a single-quoted CSS string, such as `url('...')`.
     * Quotes, backslashes and line breaks are replaced by CSS escapes so the value cannot end the string.
     * The result still needs `escape()` when it is placed in an HTML attribute.
     *
     * @param mixed $value
     * The value to escape. It is converted to a string first.
     *
     * @return string
     * The escaped value.
     */
    private static function escapeCssString($value) {
        return str_replace(
            ["\\", "'", '"', "\n", "\r"],
            ['\5c ', '\27 ', '\22 ', '\a ', '\d '],
            (string) $value
        );
    }
}
