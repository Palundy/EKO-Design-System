from abc import abstractmethod
from typing import Any

from eko_design_system.component import Component


class Field(Component):
    """
    `Field` is the shared base of the form field components `Input`, `Textarea` and `Dropdown`
    (see css/components/field.css). It is not used directly.

    It builds the parts every field has in common and leaves the control itself to the subclass:
    ```html
    <div class="field [error|success]">
        <label class="field-label" for="{id}">{label}</label>
        {control}
        <div id="{id}-info" class="field-info">{info}</div>
    </div>
    ```

    The fields live in the `eko_design_system.fields` package (e.g., `from eko_design_system.fields import Input`).
    Like every `Component`, a field returns its HTML instead of printing it.
    All text and attribute values are HTML-escaped.
    The output is identical to the PHP `EKO\\DesignSystem\\Fields\\Field`.
    """


    VALID_STATES: tuple[str, ...] = (
        "error",
        "success",
        "disabled"
    )
    """
    States accepted by the `state` argument:
    - "error": red border and red info text (`.field.error`).
    - "success": green border and green info text (`.field.success`).
    - "disabled": adds the native `disabled` attribute to the control.
    """


    def __init__(
        self,
        label: str,
        name: str | None = None,
        id: str | None = None,
        state: str | None = None,
        info: str | None = None,
        required: bool = False,
        attributes: dict[str, Any] | None = None
    )-> None:
        """
        Check the shared arguments, build the wrapper, label and info text around the control
        from `_render_control()`, and store the HTML in `html`.

        ### Parameters
        - label (`str`): The visible label text. " *" is added when `required` is `True`.
        - name (`str`, optional): The form field name. Also used as the id when `id` is not given.
        - id (`str`, optional): The element id, linked to the label and the info text. Defaults to `name`.
        - state (`str`, optional): One of `VALID_STATES`, or `None` for the default state.
        - info (`str`, optional): Help or validation text shown under the control.
        - required (`bool`, optional): If set to `True`, adds the `required` attribute.
        - attributes (`dict[str, Any]`, optional): Extra attributes for the control as name: value
        (e.g., `{"autocomplete": "email"}`). `True` writes a bare attribute, `False` or `None` leaves it out.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `state` is not an accepted value, or neither `id` nor `name` is given.
        """
        # Validate the arguments before building anything
        if state and state not in self.VALID_STATES:
            raise ValueError(f"Invalid field state: {state}")
        self._id = id if id is not None else name
        if not self._id:
            raise ValueError(f"A field needs an id or a name: {label}")

        self._label = label
        self._name = name
        self._state = state
        self._info = info
        self._required = required
        self._attributes = attributes or {}


        # Build the wrapper class: "disabled" is an attribute on the control, not a class
        css_class = "field"
        if state and state != "disabled":
            css_class += f" {state}"

        # Build the label, marking required fields
        label_text = self._escape(label) + (" *" if required else "")


        # Create and store the HTML
        html = f"<div class=\"{css_class}\">"
        html += f"<label class=\"field-label\" for=\"{self._escape(self._id)}\">{label_text}</label>"
        html += self._render_control()
        if self._has_info():
            html += f"<div id=\"{self._escape(self._info_id())}\" class=\"field-info\">{self._escape(info)}</div>"
        html += "</div>"
        self.html = html


    @abstractmethod
    def _render_control(self)-> str:
        """
        Build the HTML of the form control itself (input, textarea or dropdown).

        ### Returns
        - `str`: The control's HTML.
        """


    def _base_attributes(self)-> dict[str, Any]:
        """
        Collect the attributes every control shares: class, id, name, required, disabled
        and aria-describedby, followed by the caller's `attributes`.

        A caller's attribute with the same name replaces the shared one, but keeps its position.

        ### Returns
        - `dict[str, Any]`: The attributes as name: value, in the format `_attributes_to_html()` expects.
        """
        return {
            "class": "field-input",
            "id": self._id,
            "name": self._name,
            "required": self._required,
            "disabled": self._state == "disabled",
            "aria-describedby": self._info_id() if self._has_info() else None,
            **self._attributes,
        }


    def _has_info(self)-> bool:
        """
        Check whether the field has info text to show.

        ### Returns
        - `bool`: `True` if `info` is set and not empty.
        """
        return self._info is not None and self._info != ""


    def _info_id(self)-> str:
        """
        Build the id of the info text element.

        ### Returns
        - `str`: The id in the form "{id}-info".
        """
        return f"{self._id}-info"


    @staticmethod
    def _attributes_to_html(attributes: dict[str, Any])-> str:
        """
        Turn name: value pairs into an escaped attribute string with a leading space.

        ### Parameters
        - attributes (`dict[str, Any]`): The attributes as name: value.
        `True` writes a bare attribute (e.g., ` required`), `False` and `None` are skipped.

        ### Returns
        - `str`: The attributes as HTML (e.g., ` id="name" required`).
        """
        html = ""
        for key, value in attributes.items():
            if value is None or value is False:
                continue
            html += " " + Field._escape(key)
            if value is not True:
                html += f"=\"{Field._escape(value)}\""
        return html


    @staticmethod
    def _escape(value: Any)-> str:
        """
        Escape a value for use in HTML text or a quoted attribute.

        It matches PHP's `htmlspecialchars($value, ENT_QUOTES)`, so both languages produce the same HTML:
        Python's `html.escape()` would write a single quote as `&#x27;` instead of `&#039;`.

        ### Parameters
        - value (`Any`): The value to escape. It is converted to a string first, like PHP does:
        `None` and `False` become "", `True` becomes "1".

        ### Returns
        - `str`: The escaped value.
        """
        return (
            Field._to_string(value)
            .replace("&", "&amp;")
            .replace("<", "&lt;")
            .replace(">", "&gt;")
            .replace("\"", "&quot;")
            .replace("'", "&#039;")
        )


    @staticmethod
    def _to_string(value: Any)-> str:
        """
        Convert a value to a string the way PHP's `(string)` cast does.

        This keeps comparisons (such as the selected dropdown option) and the escaped output identical to PHP.

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
