from typing import Any

from eko_design_system.component import Component
from eko_design_system.design_system import DesignSystem


class Button(Component):
    """
    `Button` renders a design-system button (`.btn`, see css/components/button.css).
    It returns its HTML instead of printing it: use it as a string with `str(Button(...))`,
    or print it in a Jinja template with `{{ button }}`.
    Import it with `from eko_design_system import Button`.

    Examples:
    - `Button("Save")`
    - `Button("Delete", style = "destructive", state = "loading")`
    - `Button("Send", type = "submit", attributes = {"id": "send-btn"})`

    The generated class names must stay in sync with the hand-written buttons in index.html.
    The output is identical to the PHP `EKO\\DesignSystem\\Button`.
    """


    _VALID_STYLES: tuple[str, ...] = (
        "destructive",
        "positive",
        "enquiry"
    )
    """
    Colour variants accepted by the `style` argument. Each one maps to a `.btn-<style>` class.
    Leave `style` empty for the default (primary) button.
    """

    _VALID_STATES: tuple[str, ...] = (
        "disabled",
        "negative",
        "loading"
    )
    """
    States accepted by the `state` argument:
    - "disabled": adds the native `disabled` attribute.
    - "negative": outlined button (white background, coloured border and text).
    - "loading": shows a CSS spinner. Any icon is left out.
    """


    def __init__(
        self,
        label: str,
        style: str | None = None,
        icon: str | None = None,
        state: str | None = None,
        type: str = "button",
        attributes: dict[str, Any] | None = None
    )-> None:
        """
        Build the button HTML and store it in `html`.
        Pass keyword arguments so the optional parameters can be skipped.
        All text and attribute values are HTML-escaped, so user input can be passed safely.

        ### Parameters
        - label (`str`): The button text. It is escaped, so it is shown literally and cannot contain HTML.
        - style (`str`, optional): One of `_VALID_STYLES`, or `None` for the primary button.
        - icon (`str`, optional): A bundled icon's name (e.g., "envelope-outline") or the URL of your own SVG icon.
        It is shown before the label and left out while loading. See `DesignSystem.resolve_icon()`.
        - state (`str`, optional): One of `_VALID_STATES`, or `None` for the default state.
        - type (`str`, optional): The HTML `type` attribute: "button" (default), "submit" or "reset".
        - attributes (`dict[str, Any]`, optional): Extra HTML attributes as name: value (e.g., `{"id": "send-btn"}`).
        `True` writes a bare attribute, `False` or `None` leaves it out.
        Keys that match a state name are ignored, so use `state` for those.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `style` or `state` is not an accepted value, or `icon` names an unknown icon.
        """
        # Validate the style and state before building anything
        if style and style not in self._VALID_STYLES:
            raise ValueError(f"Invalid button style: {style}")
        if state and state not in self._VALID_STATES:
            raise ValueError(f"Invalid button state: {state}")


        # Build the class list: "disabled" is a native attribute, the other states are classes
        css_class = "btn"
        if style:
            css_class += f" {style} btn-{style}"
        if state and state != "disabled":
            css_class += f" {state}"

        # Collect the attributes, skipping any that should be set through `state`
        all_attributes: dict[str, Any] = {
            "type": type,
            "class": css_class,
        }
        for key, value in (attributes or {}).items():
            if key not in self._VALID_STATES:
                all_attributes[key] = value
        all_attributes["disabled"] = state == "disabled"

        # Build the content: the escaped label, with the icon before it (not possible while loading)
        content = self._escape(label)
        if icon and state != "loading":
            icon_style = "--icon-url: url('" + self._escape_css_string(DesignSystem.resolve_icon(icon)) + "')"
            # Raises a ValueError on an unknown icon name
            content = f"<span class=\"icon\" style=\"{self._escape(icon_style)}\" aria-hidden=\"true\"></span> {content}"


        # Create and store the HTML
        self.html = "<button" + self._attributes_to_html(all_attributes) + f">{content}</button>"


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
            html += " " + Button._escape(key)
            if value is not True:
                html += f"=\"{Button._escape(value)}\""
        return html


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
            Button._to_string(value)
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
            Button._to_string(value)
            .replace("\\", "\\5c ")
            .replace("'", "\\27 ")
            .replace("\"", "\\22 ")
            .replace("\n", "\\a ")
            .replace("\r", "\\d ")
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
