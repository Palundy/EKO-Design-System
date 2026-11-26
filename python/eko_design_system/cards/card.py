from typing import Any

from eko_design_system.component import Component
from eko_design_system.design_system import DesignSystem


class Card(Component):
    """
    `Card` is the shared base of the card components `ProductCard`, `ContactCard` and, through `ImageCard`,
    the image cards (see assets/css/components/product-card.css, contact-card.css and image-card.css). It is not used directly.
    The cards live in the `eko_design_system.cards` package (e.g., `from eko_design_system.cards import ProductCard`).

    It holds what every card has in common: the escaping, icon and attribute helpers.
    Like every `Component`, a card returns its HTML instead of printing it.
    The output is identical to the PHP `EKO\\DesignSystem\\Cards\\Card`.
    """


    @staticmethod
    def _render_icon(icon: str)-> str:
        """
        Build a decorative icon (see assets/css/components/icon.css). It is hidden from screen readers.

        ### Parameters
        - icon (`str`): A bundled icon's name (e.g., "envelope-outline") or the URL of an SVG icon.
        See `DesignSystem.resolve_icon()`.

        ### Returns
        - `str`: The icon's HTML.

        ### Raises
        - `ValueError`: If `icon` names an unknown icon.
        """
        style = "--icon-url: url('" + Card._escape_css_string(DesignSystem.resolve_icon(icon)) + "')"
        # Raises a ValueError on an unknown icon name
        return f"<span class=\"icon\" style=\"{Card._escape(style)}\" aria-hidden=\"true\"></span>"


    @staticmethod
    def _attributes_to_html(attributes: dict[str, Any])-> str:
        """
        Turn name: value pairs into an escaped attribute string with a leading space.

        ### Parameters
        - attributes (`dict[str, Any]`): The attributes as name: value.
        `True` writes a bare attribute (e.g., ` disabled`), `False` and `None` are skipped.

        ### Returns
        - `str`: The attributes as HTML (e.g., ` type="button" disabled`).
        """
        html = ""
        for key, value in attributes.items():
            if value is None or value is False:
                continue
            html += " " + Card._escape(key)
            if value is not True:
                html += f"=\"{Card._escape(value)}\""
        return html


    @staticmethod
    def _is_filled(value: Any)-> bool:
        """
        Check whether an optional text value was given.

        ### Parameters
        - value (`Any`): The value to check.

        ### Returns
        - `bool`: `True` if `value` is not `None` and not an empty string.
        """
        return value is not None and value != ""


    @staticmethod
    def _escape(value: Any)-> str:
        """
        Escape a value for use in HTML text or a quoted attribute.

        It matches PHP's `htmlspecialchars($value, ENT_QUOTES)`, so both languages produce the same HTML.

        ### Parameters
        - value (`Any`): The value to escape. It is converted to a string first, like PHP does (see `_to_string()`).

        ### Returns
        - `str`: The escaped value.
        """
        return (
            Card._to_string(value)
            .replace("&", "&amp;")
            .replace("<", "&lt;")
            .replace(">", "&gt;")
            .replace("\"", "&quot;")
            .replace("'", "&#039;")
        )


    @staticmethod
    def _escape_css_string(value: Any)-> str:
        """
        Escape a value for use inside a single-quoted CSS string, such as `url('...')`.

        Quotes, backslashes and line breaks are replaced by CSS escapes so the value cannot end the string.
        The result still needs `_escape()` when it is placed in an HTML attribute.

        ### Parameters
        - value (`Any`): The value to escape. It is converted to a string first.

        ### Returns
        - `str`: The escaped value.
        """
        # The backslash goes first, so the backslashes of the other escapes are not escaped again
        return (
            Card._to_string(value)
            .replace("\\", "\\5c ")
            .replace("'", "\\27 ")
            .replace("\"", "\\22 ")
            .replace("\n", "\\a ")
            .replace("\r", "\\d ")
        )


    @staticmethod
    def _to_string(value: Any)-> str:
        """
        Convert a value to a string the way PHP's `(string)` cast does, so the output matches the PHP components.

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
