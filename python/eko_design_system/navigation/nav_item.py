from typing import Any

from eko_design_system.component import Component


class NavItem(Component):
    """
    `NavItem` renders one link of the main menu, followed by a yellow chevron (`.nav-item`, see css/components/nav-item.css).
    It returns its HTML instead of printing it: use it as a string with `str(NavItem(...))`,
    or print it in a Jinja template with `{{ item }}`.
    Import it with `from eko_design_system.navigation import NavItem`.

    Examples:
    - `NavItem("Products", "/products")`
    - `NavItem("Products", "/products", active = True)` for the section the visitor is in.

    All text and attribute values are HTML-escaped.
    The output is identical to the PHP `EKO\\DesignSystem\\Navigation\\NavItem`.
    """


    def __init__(
        self,
        label: str,
        url: str,
        active: bool = False
    )-> None:
        """
        Build the navigation item HTML and store it in `html`.

        ### Parameters
        - label (`str`): The menu text.
        - url (`str`): Where the item links to.
        - active (`bool`, optional): If set to `True`, marks the item as the current section:
        bold with a blue line underneath (`aria-current="page"`).

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `label` or `url` is empty.
        """
        # Validate the arguments before building anything
        if label is None or label == "" or url is None or url == "":
            raise ValueError("A navigation item needs a label and a url")


        # Create and store the HTML
        current = " aria-current=\"page\"" if active else ""
        self.html = f"<a class=\"nav-item\" href=\"{self._escape(url)}\"{current}>{self._escape(label)}</a>"


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
            NavItem._to_string(value)
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
