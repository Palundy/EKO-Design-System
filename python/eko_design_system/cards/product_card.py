from typing import Any

from .card import Card


class ProductCard(Card):
    """
    `ProductCard` renders a product card (`.product-card`, see css/components/product-card.css).
    It returns its HTML instead of printing it: use it as a string with `str(ProductCard(...))`,
    or print it in a Jinja template with `{{ card }}`.
    Import it with `from eko_design_system.cards import ProductCard`.

    Every part except the title is optional and is left out when not given:
    ```html
    <div class="product-card">
        <div class="card-image"><img src="{image}" alt="{image_alt}"></div>
        <div class="card-label">{label}</div>
        <div class="card-title">{title}</div>
        <div class="card-description">{description}</div>
        <table class="card-table">...</table>
        <a class="card-cta" href="{cta_url}">{cta}</a>
    </div>
    ```

    The table is passed as name: value pairs, one pair per row:
    ```python
    ProductCard(
        title = "MS-80SH",
        label = "Class A",
        description = "Class A pyranometer with ventilator and heater for utility-scale PV",
        image = "assets/images/ms80sh.png",
        table = {
            "ISO 9060:2018": "spectrally flat, Class A",
            "Warranty": "5 years",
        },
        cta = "View product",
        cta_url = "/products/ms-80sh"
    )
    ```
    If two rows share the same name, or a row needs more than two cells, pass the table as a list of rows instead:
    `table = [["Warranty", "5 years"], ["Warranty", "10 years (extended)"]]`.

    All text and attribute values are HTML-escaped. The generated class names must stay in sync
    with the hand-written product cards in index.html.
    """


    def __init__(
        self,
        title: str,
        label: str | None = None,
        description: str | None = None,
        image: str | None = None,
        image_alt: str | None = None,
        table: dict[Any, Any] | list[Any] | None = None,
        cta: str | None = None,
        cta_url: str | None = None
    )-> None:
        """
        Build the product card HTML and store it in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - title (`str`): The product name, shown in large bold text.
        - label (`str`, optional): A short tag shown above the title (e.g., "Class A").
        - description (`str`, optional): A short text shown under the title.
        - image (`str`, optional): The image URL (e.g., "assets/images/ms80sh.png"). Without it, the card has no image area.
        - image_alt (`str`, optional): The image's alt text. Defaults to `title`.
        - table (`dict[Any, Any] | list[Any]`, optional): The specification rows, either as a dict of name: value
        (e.g., `{"Warranty": "5 years"}`) or as a list of rows, each a list of cells (e.g., `[["Warranty", "5 years"]]`).
        A dict value that is a list is used as the row's cells. Without rows, the table is left out.
        - cta (`str`, optional): The call-to-action text shown at the bottom of the card.
        - cta_url (`str`, optional): Where the call to action links to. With a URL the call to action is an `<a>`, without one a `<div>`.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `title` is empty, or a table row is not a string, number or list of cells.
        """
        # Validate the arguments before building anything
        if not self._is_filled(title):
            raise ValueError("A product card needs a title")


        # Build the card from top to bottom, skipping the parts that were not given
        html = "<div class=\"product-card\">"
        if self._is_filled(image):
            alt = image_alt if image_alt is not None else title
            html += f"<div class=\"card-image\"><img src=\"{self._escape(image)}\" alt=\"{self._escape(alt)}\"></div>"
        if self._is_filled(label):
            html += f"<div class=\"card-label\">{self._escape(label)}</div>"
        html += f"<div class=\"card-title\">{self._escape(title)}</div>"
        if self._is_filled(description):
            html += f"<div class=\"card-description\">{self._escape(description)}</div>"
        if table:
            html += self._render_table(table)
            # Raises a ValueError on an invalid row
        if self._is_filled(cta):
            html += self._render_cta(cta, cta_url)
        html += "</div>"


        # Store the HTML
        self.html = html


    @staticmethod
    def _render_table(table: dict[Any, Any] | list[Any])-> str:
        """
        Build the specification table.

        A dict is read as name: value, a list as index: row, just like PHP reads `$name => $value` from one array.

        ### Parameters
        - table (`dict[Any, Any] | list[Any]`): The rows, as described for the `table` argument of `__init__`.

        ### Returns
        - `str`: The table's HTML.

        ### Raises
        - `ValueError`: If a row is not a string, number or list of cells.
        """
        rows = table.items() if isinstance(table, dict) else enumerate(table)

        html = "<table class=\"card-table\">"
        for name, value in rows:
            # A list row gives its own cells, a name: value row gives two cells
            if isinstance(value, (list, tuple)):
                cells = value
            elif isinstance(value, (str, int, float, bool)):
                cells = [name, value]
            else:
                raise ValueError(f"Invalid product card table row: {ProductCard._to_string(name)}")

            html += "<tr>"
            for cell in cells:
                html += f"<td>{ProductCard._escape(cell)}</td>"
            html += "</tr>"
        html += "</table>"
        return html


    @staticmethod
    def _render_cta(
        cta: str,
        cta_url: str | None
    )-> str:
        """
        Build the call to action: a link when a URL is given, otherwise plain text.

        ### Parameters
        - cta (`str`): The call-to-action text.
        - cta_url (`str | None`): The link target, or `None` for plain text.

        ### Returns
        - `str`: The call to action's HTML.
        """
        if ProductCard._is_filled(cta_url):
            return f"<a class=\"card-cta\" href=\"{ProductCard._escape(cta_url)}\">{ProductCard._escape(cta)}</a>"
        return f"<div class=\"card-cta\">{ProductCard._escape(cta)}</div>"
