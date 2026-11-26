from typing import Any

from eko_design_system.component import Component


class SpecTable(Component):
    """
    `SpecTable` renders a titled specifications table (`.spec-table`, see css/components/spec-table.css).
    It returns its HTML instead of printing it: use it as a string with `str(SpecTable(...))`,
    or print it in a Jinja template with `{{ table }}`.
    Import it with `from eko_design_system import SpecTable`.

    Rows are passed as label: value pairs, one pair per row. A single value gives a two-column key/value table,
    a list of values gives a comparison table with one column per value:
    ```python
    SpecTable(
        title = "Specifications",
        rows = {
            "Wavelength Range (nm)": "285 to 3000",
            "Ingress Protection": "IP 67",
        }
    )

    SpecTable(
        title = "ISO Specifications",
        header = ["ISO 9060:2018 Parameters", "CLASS A", "MS-80SH"],
        rows = {
            "Response time 95%": ["< 10s", "< 0.5s"],
            "Spectral Error": ["± 0.5%", "± 0.2%"],
        }
    )
    ```
    If two rows share the same label, pass the rows as a list of rows with the label first:
    `rows = [["Warranty", "5 years"], ["Warranty", "10 years (extended)"]]`.

    All text is HTML-escaped. The generated class names must stay in sync with the hand-written tables in index.html.
    The output is identical to the PHP `EKO\\DesignSystem\\SpecTable`.
    """


    _VALID_HEADING_LEVELS: tuple[int, ...] = (2, 3, 4, 5, 6)
    """Heading levels accepted by the `heading_level` argument for the title (`<h2>` to `<h6>`)."""


    def __init__(
        self,
        rows: dict[Any, Any] | list[Any],
        title: str | None = None,
        header: list[Any] | None = None,
        heading_level: int = 2
    )-> None:
        """
        Build the table HTML and store it in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - rows (`dict[Any, Any] | list[Any]`): The table rows. A dict holds label: value (e.g., `{"Warranty": "5 years"}`)
        or label: list of values (e.g., `{"Response time 95%": ["< 10s", "< 0.5s"]}`).
        A list holds rows with the label first (e.g., `[["Warranty", "5 years"]]`).
        Every row must have the same number of cells.
        - title (`str`, optional): The title above the table. Without it, the title and its line are left out.
        - header (`list[Any]`, optional): The cells of the header row, including the first (label) column
        (e.g., `["ISO 9060:2018 Parameters", "CLASS A", "MS-80SH"]`). Without it, the table has no header row.
        - heading_level (`int`, optional): The heading element used for the title: 2 (default) to 6 for `<h2>` to `<h6>`.
        Choose the level that fits the page outline; it does not change the look. It must be an `int`.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `rows` is empty, a row or header is not valid, the rows and header do not all have
        the same number of cells, or `heading_level` is not accepted.
        """
        header = header or []

        # Validate the arguments before building anything
        if type(heading_level) is not int or heading_level not in self._VALID_HEADING_LEVELS:
            raise ValueError(f"Invalid spec table heading level: {self._to_string(heading_level)}")
        if not rows:
            raise ValueError("A spec table needs at least one row")

        # Turn every row into a list of cells, with the label first. A dict is read as label: value,
        # a list as index: row, just like PHP reads `$label => $value` from one array.
        entries = rows.items() if isinstance(rows, dict) else enumerate(rows)
        body_rows = [self._to_cells(label, value) for label, value in entries]
        # Raises a ValueError on an invalid row

        # Every row, and the header if given, must have the same number of cells
        column_count = len(body_rows[0])
        for cells in body_rows:
            if len(cells) != column_count:
                raise ValueError(f"Every spec table row needs {column_count} cells: {self._to_string(cells[0])}")
        if header and len(header) != column_count:
            raise ValueError(f"The spec table header needs {column_count} cells")


        # Build the section: the optional title, then the table inside its panel
        html = "<section class=\"spec-table\">"
        if title is not None and title != "":
            html += f"<h{heading_level} class=\"spec-table-title\">{self._escape(title)}</h{heading_level}>"
        html += "<div class=\"spec-table-panel\"><table>"

        # Add the optional header row
        if header:
            html += "<thead><tr>"
            for cell in header:
                html += f"<th>{self._escape(cell)}</th>"
            html += "</tr></thead>"

        # Add the body rows: the label as a row header, followed by the values
        html += "<tbody>"
        for label, *values in body_rows:
            html += f"<tr><th scope=\"row\">{self._escape(label)}</th>"
            for cell in values:
                html += f"<td>{self._escape(cell)}</td>"
            html += "</tr>"
        html += "</tbody></table></div></section>"


        # Store the HTML
        self.html = html


    @staticmethod
    def _to_cells(
        label: Any,
        value: Any
    )-> list[Any]:
        """
        Turn one entry of `rows` into a list of cells with the label first.

        ### Parameters
        - label (`Any`): The dict key of the entry (the label), or the list index for a list row.
        - value (`Any`): The value of the entry: a value, a list of values, or (for a list row) the whole row.

        ### Returns
        - `list[Any]`: The cells of the row, label first.

        ### Raises
        - `ValueError`: If the entry is not a valid row.
        """
        is_list = isinstance(value, (list, tuple, dict))
        values = list(value.values()) if isinstance(value, dict) else list(value) if is_list else []

        # A list row: the whole row is given, with the label first
        if type(label) is int and is_list:
            if len(values) < 2:
                raise ValueError("A spec table row needs a label and at least one value")
            return values

        # A label with one value, or with a list of values
        if is_list:
            if not values:
                raise ValueError(f"A spec table row needs at least one value: {SpecTable._to_string(label)}")
            return [label, *values]
        if isinstance(value, (str, int, float, bool)):
            return [label, value]
        raise ValueError(f"Invalid spec table row: {SpecTable._to_string(label)}")


    @staticmethod
    def _escape(value: Any)-> str:
        """
        Escape a value for use in HTML text or a quoted attribute, like PHP's `htmlspecialchars($value, ENT_QUOTES)`.

        ### Parameters
        - value (`Any`): The value to escape. It is converted to a string first, like PHP does (see `_to_string()`).

        ### Returns
        - `str`: The escaped value.
        """
        return (
            SpecTable._to_string(value)
            .replace("&", "&amp;")
            .replace("<", "&lt;")
            .replace(">", "&gt;")
            .replace("\"", "&quot;")
            .replace("'", "&#039;")
        )


    @staticmethod
    def _to_string(value: Any)-> str:
        """
        Convert a value to a string the way PHP's `(string)` cast does, so the output matches the PHP component.

        ### Parameters
        - value (`Any`): The value to convert.

        ### Returns
        - `str`: "" for `None` and `False`, "1" for `True`, "2" for a whole float such as `2.0`,
        and `str(value)` for anything else.
        """
        if value is None or value is False:
            return ""
        if value is True:
            return "1"
        if isinstance(value, float) and value.is_integer() and abs(value) < 1e15:
            return str(int(value))
        return str(value)
