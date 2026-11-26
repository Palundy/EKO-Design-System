<?php
namespace EKO\DesignSystem\Navigation;

use InvalidArgumentException;


/**
 * `Breadcrumb` renders the trail to the current page (`.breadcrumb`, see css/components/breadcrumb.css).
 * It is echoed straight away from the constructor, so creating it inside a template prints it at that spot.
 *
 * Pass the trail from the home page to the current page as label => URL pairs. The last item is the current page;
 * give it without a URL:
 * ```php
 * new Breadcrumb([
 *     "Home" => "/",
 *     "Pyranometers" => "/pyranometers",
 *     "MS-80SH" => "/products/ms-80sh",
 *     "Talk to an Expert",
 * ]);
 * ```
 *
 * All text and attribute values are HTML-escaped.
 */
class Breadcrumb {


    /** The HTML that was generated and echoed, kept so callers can reuse it. */
    public $html = null;


    /**
     * Builds the breadcrumb HTML, stores it in `$html` and echoes it.
     *
     * @param array $items
     * The trail, in order. Each entry is label => URL (a link), or just a label (no link).
     * The last entry is marked as the current page, with or without a URL.
     *
     * @param string $label [optional]
     * The accessible name of the navigation landmark, read out by screen readers. Defaults to "Breadcrumb".
     *
     * @throws InvalidArgumentException if `$items` is empty.
     */
    public function __construct($items, $label = 'Breadcrumb') {
        // Validate the arguments before building anything
        if (!$items) {
            throw new InvalidArgumentException("A breadcrumb needs at least one item");
        }


        // Build the list: links for the trail, the last item marked as the current page
        $html = "<nav class=\"breadcrumb\" aria-label=\"" . self::escape($label) . "\"><ol>";
        $lastIndex = count($items) - 1;
        $index = 0;
        foreach ($items as $key => $value) {
            // A string key is a label with a URL, a number key means the value is a label without a URL
            [$text, $url] = is_string($key) ? [$key, $value] : [$value, null];
            $current = $index === $lastIndex ? " aria-current=\"page\"" : "";
            $content = self::escape($text);

            if ($url !== null && $url !== '') {
                $html .= "<li><a href=\"" . self::escape($url) . "\"$current>$content</a></li>";
            } else {
                $html .= "<li$current>$content</li>";
            }
            $index++;
        }
        $html .= "</ol></nav>";


        // Store and print the HTML
        $this->html = $html;
        echo $html;
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
}
