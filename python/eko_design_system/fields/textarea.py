from typing import Any

from .field import Field


class Textarea(Field):
    """
    `Textarea` renders a multi-line `<textarea>` field. Its height comes from `--field-textarea-height`.
    It returns its HTML instead of printing it: use it as a string with `str(Textarea(...))`,
    or print it in a Jinja template with `{{ field }}`.
    Import it with `from eko_design_system.fields import Textarea`.

    Examples:
    - `Textarea("Message", name = "message", placeholder = "Your question", info = "Max. 500 characters.")`
    - `Textarea("Message", name = "message", state = "error", info = "Please fill in this field.")`
    """


    def __init__(
        self,
        label: str,
        name: str | None = None,
        id: str | None = None,
        state: str | None = None,
        info: str | None = None,
        required: bool = False,
        attributes: dict[str, Any] | None = None,
        placeholder: str | None = None,
        value: str | None = None
    )-> None:
        """
        Build the textarea field and store the HTML in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - label (`str`): The visible label text. " *" is added when `required` is `True`.
        - name (`str`, optional): The form field name. Also used as the id when `id` is not given.
        - id (`str`, optional): The element id, linked to the label and the info text. Defaults to `name`.
        - state (`str`, optional): "error", "success", "disabled", or `None` for the default state.
        - info (`str`, optional): Help or validation text shown under the textarea.
        - required (`bool`, optional): If set to `True`, adds the `required` attribute.
        - attributes (`dict[str, Any]`, optional): Extra attributes for the textarea as name: value
        (e.g., `{"maxlength": 500}`). `True` writes a bare attribute, `False` or `None` leaves it out.
        - placeholder (`str`, optional): The placeholder text.
        - value (`str`, optional): The initial content.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `state` is not an accepted value, or neither `id` nor `name` is given.
        """
        # Store the textarea's own arguments first: the base class renders the control while it is created
        self._placeholder = placeholder
        self._value = value

        super().__init__(
            label = label,
            name = name,
            id = id,
            state = state,
            info = info,
            required = required,
            attributes = attributes
        )
        # Raises a ValueError on an invalid state or a missing id and name


    def _render_control(self)-> str:
        """
        Build the `<textarea>` element.

        ### Returns
        - `str`: The textarea's HTML.
        """
        attributes = {
            "placeholder": self._placeholder,
            **self._base_attributes(),
        }
        return (
            "<textarea" + self._attributes_to_html(attributes) + ">"
            + self._escape(self._value if self._value is not None else "")
            + "</textarea>"
        )
