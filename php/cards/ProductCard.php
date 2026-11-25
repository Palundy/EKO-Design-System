<?php
namespace Cards;

use InvalidArgumentException;


/**
 * `ProductCard` renders a product card (`.product-card`, see css/components/product-card.css).
 * It is echoed straight away from the constructor, so creating it inside a template prints it at that spot.
 *
 * Every part except the title is optional and is left out when not given:
 * ```html
 * <div class="product-card">
 *     <div class="card-image"><img src="{image}" alt="{imageAlt}"></div>
 *     <div class="card-label">{label}</div>
 *     <div class="card-title">{title}</div>
 *     <div class="card-description">{description}</div>
 *     <table class="card-table">...</table>
 *     <a class="card-cta" href="{ctaUrl}">{cta}</a>
 * </div>
 * ```
 *
 * The table is passed as name => value pairs, one pair per row:
 * ```php
 * new ProductCard(
 *     title: "MS-80SH",
 *     label: "Class A",
 *     description: "Class A pyranometer with ventilator and heater for utility-scale PV",
 *     image: "assets/images/ms80sh.png",
 *     table: [
 *         "ISO 9060:2018" => "spectrally flat, Class A",
 *         "Warranty" => "5 years",
 *     ],
 *     cta: "View product",
 *     ctaUrl: "/products/ms-80sh"
 * );
 * ```
 * If two rows share the same name, or a row needs more than two cells, write that row as a list instead:
 * `table: [["Warranty", "5 years"], ["Warranty", "10 years (extended)"]]`. Both forms can be mixed.
 *
 * All text and attribute values are HTML-escaped. The generated class names must stay in sync
 * with the hand-written product cards in index.html.
 */
class ProductCard extends Card {


    /**
     * Builds the product card HTML, stores it in `$html` and echoes it.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param string $title
     * The product name, shown in large bold text.
     *
     * @param string|null $label [optional]
     * A short tag shown above the title (e.g., "Class A").
     *
     * @param string|null $description [optional]
     * A short text shown under the title.
     *
     * @param string|null $image [optional]
     * The image URL (e.g., "assets/images/ms80sh.png"). Without it, the card has no image area.
     *
     * @param string|null $imageAlt [optional]
     * The image's alt text. Defaults to `$title`.
     *
     * @param array $table [optional]
     * The specification rows. Each entry is either name => value (e.g., `"Warranty" => "5 years"`)
     * or a list of cells (e.g., `["Warranty", "5 years"]`). Without rows, the table is left out.
     *
     * @param string|null $cta [optional]
     * The call-to-action text shown at the bottom of the card.
     *
     * @param string|null $ctaUrl [optional]
     * Where the call to action links to. With a URL the call to action is an `<a>`, without one a `<div>`.
     *
     * @throws InvalidArgumentException if `$title` is empty, or a table row is not a string, number or list of cells.
     */
    public function __construct(
        $title,
        $label = null,
        $description = null,
        $image = null,
        $imageAlt = null,
        $table = [],
        $cta = null,
        $ctaUrl = null
    ) {
        // Validate the arguments before building anything
        if (!self::isFilled($title)) {
            throw new InvalidArgumentException("A product card needs a title");
        }


        // Build the card from top to bottom, skipping the parts that were not given
        $html = "<div class=\"product-card\">";
        if (self::isFilled($image)) {
            $alt = $imageAlt ?? $title;
            $html .= "<div class=\"card-image\"><img src=\"" . self::escape($image) . "\" alt=\"" . self::escape($alt) . "\"></div>";
        }
        if (self::isFilled($label)) {
            $html .= "<div class=\"card-label\">" . self::escape($label) . "</div>";
        }
        $html .= "<div class=\"card-title\">" . self::escape($title) . "</div>";
        if (self::isFilled($description)) {
            $html .= "<div class=\"card-description\">" . self::escape($description) . "</div>";
        }
        if ($table) {
            $html .= self::renderTable($table);
        }
        if (self::isFilled($cta)) {
            $html .= self::renderCta($cta, $ctaUrl);
        }
        $html .= "</div>";


        // Store and print the HTML
        $this->output($html);
    }

    /**
     * Builds the specification table.
     *
     * @param array $table
     * The rows, as described for the `$table` argument of the constructor.
     *
     * @return string
     * The table's HTML.
     *
     * @throws InvalidArgumentException if a row is not a string, number or list of cells.
     */
    private static function renderTable($table) {
        $html = "<table class=\"card-table\">";
        foreach ($table as $name => $value) {
            // A list row gives its own cells, a name => value row gives two cells
            if (is_array($value)) {
                $cells = $value;
            } elseif (is_scalar($value)) {
                $cells = [$name, $value];
            } else {
                throw new InvalidArgumentException("Invalid product card table row: $name");
            }

            $html .= "<tr>";
            foreach ($cells as $cell) {
                $html .= "<td>" . self::escape($cell) . "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        return $html;
    }

    /**
     * Builds the call to action: a link when a URL is given, otherwise plain text.
     *
     * @param string $cta
     * The call-to-action text.
     *
     * @param string|null $ctaUrl
     * The link target, or `null` for plain text.
     *
     * @return string
     * The call to action's HTML.
     */
    private static function renderCta($cta, $ctaUrl) {
        if (self::isFilled($ctaUrl)) {
            return "<a class=\"card-cta\" href=\"" . self::escape($ctaUrl) . "\">" . self::escape($cta) . "</a>";
        }
        return "<div class=\"card-cta\">" . self::escape($cta) . "</div>";
    }
}
