<?php
namespace EKO\DesignSystem;

use InvalidArgumentException;


/**
 * `SpecTable` renders a titled specifications table (`.spec-table`, see css/components/spec-table.css).
 * The table is echoed straight away from the constructor, so creating it inside a template prints it at that spot.
 *
 * Rows are passed as label => value pairs, one pair per row. A single value gives a two-column key/value table,
 * a list of values gives a comparison table with one column per value:
 * ```php
 * new SpecTable(
 *     title: "Specifications",
 *     rows: [
 *         "Wavelength Range (nm)" => "285 to 3000",
 *         "Ingress Protection" => "IP 67",
 *     ]
 * );
 *
 * new SpecTable(
 *     title: "ISO Specifications",
 *     header: ["ISO 9060:2018 Parameters", "CLASS A", "MS-80SH"],
 *     rows: [
 *         "Response time 95%" => ["< 10s", "< 0.5s"],
 *         "Spectral Error" => ["± 0.5%", "± 0.2%"],
 *     ]
 * );
 * ```
 * If two rows share the same label, write those rows as a list with the label first:
 * `rows: [["Warranty", "5 years"], ["Warranty", "10 years (extended)"]]`. Both forms can be mixed.
 * Also use the list form when a label is a number (e.g., a year): PHP turns a key like "2023" into the number 2023,
 * so `"2023" => ["a", "b"]` would be read as a list row without a label.
 *
 * All text is HTML-escaped. The generated class names must stay in sync with the hand-written tables in index.html.
 */
class SpecTable {


    /** Heading levels accepted by the `$headingLevel` argument for the title (`<h2>` to `<h6>`). */
    private const VALID_HEADING_LEVELS = [2, 3, 4, 5, 6];


    /** The HTML that was generated and echoed, kept so callers can reuse it. */
    public $html = null;


    /**
     * Builds the table HTML, stores it in `$html` and echoes it.
     * Pass named arguments (PHP 8+) so the optional parameters can be skipped.
     *
     * @param array $rows
     * The table rows. Each entry is either label => value (e.g., `"Warranty" => "5 years"`),
     * label => list of values (e.g., `"Response time 95%" => ["< 10s", "< 0.5s"]`),
     * or a list with the label first (e.g., `["Warranty", "5 years"]`).
     * Every row must have the same number of cells.
     *
     * @param string|null $title [optional]
     * The title above the table. Without it, the title and its line are left out.
     *
     * @param array $header [optional]
     * The cells of the header row, including the first (label) column
     * (e.g., `["ISO 9060:2018 Parameters", "CLASS A", "MS-80SH"]`). Without it, the table has no header row.
     *
     * @param int $headingLevel [optional]
     * The heading element used for the title: 2 (default) to 6 for `<h2>` to `<h6>`.
     * Choose the level that fits the page outline; it does not change the look.
     *
     * @throws InvalidArgumentException if `$rows` is empty, a row or header is not valid,
     * the rows and header do not all have the same number of cells, or `$headingLevel` is not accepted.
     */
    public function __construct(
        $rows,
        $title = null,
        $header = [],
        $headingLevel = 2
    ) {
        // Validate the arguments before building anything
        if (!in_array($headingLevel, self::VALID_HEADING_LEVELS, true)) {
            throw new InvalidArgumentException("Invalid spec table heading level: $headingLevel");
        }
        if (!$rows) {
            throw new InvalidArgumentException("A spec table needs at least one row");
        }

        // Turn every row into a list of cells, with the label first
        $bodyRows = [];
        foreach ($rows as $label => $value) {
            $bodyRows[] = self::toCells($label, $value);
        }

        // Every row, and the header if given, must have the same number of cells
        $columnCount = count($bodyRows[0]);
        foreach ($bodyRows as $cells) {
            if (count($cells) !== $columnCount) {
                throw new InvalidArgumentException("Every spec table row needs $columnCount cells: " . $cells[0]);
            }
        }
        if ($header && count($header) !== $columnCount) {
            throw new InvalidArgumentException("The spec table header needs $columnCount cells");
        }


        // Build the section: the optional title, then the table inside its panel
        $html = "<section class=\"spec-table\">";
        if ($title !== null && $title !== '') {
            $html .= "<h$headingLevel class=\"spec-table-title\">" . self::escape($title) . "</h$headingLevel>";
        }
        $html .= "<div class=\"spec-table-panel\"><table>";

        // Add the optional header row
        if ($header) {
            $html .= "<thead><tr>";
            foreach ($header as $cell) {
                $html .= "<th>" . self::escape($cell) . "</th>";
            }
            $html .= "</tr></thead>";
        }

        // Add the body rows: the label as a row header, followed by the values
        $html .= "<tbody>";
        foreach ($bodyRows as $cells) {
            $html .= "<tr><th scope=\"row\">" . self::escape(array_shift($cells)) . "</th>";
            foreach ($cells as $cell) {
                $html .= "<td>" . self::escape($cell) . "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody></table></div></section>";


        // Store and print the HTML
        $this->html = $html;
        echo $html;
    }



    /**
     * Turns one entry of `$rows` into a list of cells with the label first.
     *
     * @param int|string $label
     * The array key of the entry: the label, or a number for a list row.
     *
     * @param mixed $value
     * The array value of the entry: a value, a list of values, or (for a list row) the whole row.
     *
     * @return array
     * The cells of the row, label first.
     *
     * @throws InvalidArgumentException if the entry is not a valid row.
     */
    private static function toCells($label, $value) {
        // A list row: the whole row is given, with the label first
        if (is_int($label) && is_array($value)) {
            if (count($value) < 2) {
                throw new InvalidArgumentException("A spec table row needs a label and at least one value");
            }
            return array_values($value);
        }

        // A label with one value, or with a list of values
        if (is_array($value)) {
            if (!$value) {
                throw new InvalidArgumentException("A spec table row needs at least one value: $label");
            }
            return array_merge([$label], array_values($value));
        }
        if (is_scalar($value)) {
            return [$label, $value];
        }
        throw new InvalidArgumentException("Invalid spec table row: $label");
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
