from typing import Any

from eko_design_system.component import Component


class Breadcrumb(Component):
    """
    `Breadcrumb` renders the trail to the current page (`.breadcrumb`, see assets/css/components/breadcrumb.css).
    It returns its HTML instead of printing it: use it as a string with `str(Breadcrumb(...))`,
    or print it in a Jinja template with `{{ breadcrumb }}`.
    Import it with `from eko_design_system.navigation import Breadcrumb`.

    Pass the trail from the home page to the current page as label: URL pairs. The last item is the current page;
    give it without a URL:
    ```python
    Breadcrumb({
        "Home": "/",
        "Pyranometers": "/pyranometers",
        "MS-80SH": "/products/ms-80sh",
        "Talk to an Expert": None,
    })
    ```

    All text and attribute values are HTML-escaped.
    The output is identical to the PHP `EKO\\DesignSystem\\Navigation\\Breadcrumb`.
    """


    def __init__(
        self,
        items: dict[Any, Any] | list[Any],
        label: str = "Breadcrumb"
    )-> None:
        """
        Build the breadcrumb HTML and store it in `html`.

        ### Parameters
        - items (`dict[Any, Any] | list[Any]`): The trail, in order. In a dict, each entry is label: URL (a link),
        or label: `None` (no link). A list holds labels without links.
        The last entry is marked as the current page, with or without a URL.
        - label (`str`, optional): The accessible name of the navigation landmark, read out by screen readers.
        Defaults to "Breadcrumb".

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `items` is empty.
        """
        # Validate the arguments before building anything
        if not items:
            raise ValueError("A breadcrumb needs at least one item")


        # Build the list: links for the trail, the last item marked as the current page
        html = f"<nav class=\"breadcrumb\" aria-label=\"{self._escape(label)}\"><ol>"
        last_index = len(items) - 1
        entries = items.items() if isinstance(items, dict) else enumerate(items)
        for index, (key, value) in enumerate(entries):
            # A string key is a label with a URL, a number key means the value is a label without a URL
            text, url = (key, value) if isinstance(key, str) else (value, None)
            current = " aria-current=\"page\"" if index == last_index else ""
            content = self._escape(text)

            if url is not None and url != "":
                html += f"<li><a href=\"{self._escape(url)}\"{current}>{content}</a></li>"
            else:
                html += f"<li{current}>{content}</li>"
        html += "</ol></nav>"


        # Store the HTML
        self.html = html


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
            Breadcrumb._to_string(value)
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
