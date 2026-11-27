from typing import Any

from eko_design_system.component import Component
from eko_design_system.design_system import DesignSystem


class StatusTracker(Component):
    """
    `StatusTracker` renders the steps of a process, such as a software deployment, with a progress bar underneath
    (`.ekodata-status-tracker`, see assets/addons/eko-data/css/components/status-tracker.css). Part of the EKO Data addon,
    so enable it first with `DesignSystem.enable_addon(EkoData)`, or pass `addons = [EkoData]` to the Flask extension.
    It returns its HTML instead of printing it: use it as a string with `str(StatusTracker(...))`,
    or print it in a Jinja template with `{{ tracker }}`.
    Import it with `from eko_design_system.addons.eko_data import StatusTracker`.

    Example:
    ```python
    StatusTracker(
        title = "Deployment status",
        description = "Track the deployment pipeline for this release.",
        icon = "list-ul-solid",
        action = Button("Verify", state = "negative"),
        steps = [
            {"label": "Release published", "status": "done", "detail": "2026-11-27, 11:47:27"},
            {"label": "Workflow: Image build done", "status": "active"},
            {"label": "Deployment done!"},
        ]
    )
    ```

    The progress bar shows the share of steps that are done. A failed step colours it red.
    All text and attribute values are HTML-escaped.
    The output is identical to the PHP `EKO\\DesignSystem\\Addons\\EkoData\\StatusTracker`.
    """


    _VALID_STATUSES: dict[str, str] = {
        "done": "Done",
        "active": "In progress",
        "pending": "Pending",
        "error": "Failed",
    }
    """
    Step statuses accepted in a step's "status" key, each with the words screen readers hear before the label:
    - "done": a filled marker.
    - "active": in progress, a spinning ring. The step gets `aria-current="step"`.
    - "pending": not started yet, an empty ring. The default.
    - "error": failed, a red marker with an exclamation mark. The progress bar turns red.
    """

    _VALID_STEP_KEYS: tuple[str, ...] = (
        "label",
        "status",
        "detail",
    )
    """The keys a step accepts."""

    _VALID_HEADING_LEVELS: tuple[int, ...] = (1, 2, 3, 4, 5, 6)
    """Heading levels accepted by the `heading_level` argument (`<h1>` to `<h6>`)."""


    def __init__(
        self,
        title: str,
        steps: list[dict[str, Any]],
        description: str | None = None,
        icon: str | None = None,
        action: Component | None = None,
        heading_level: int = 2,
        attributes: dict[str, Any] | None = None
    )-> None:
        """
        Build the status tracker HTML and store it in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - title (`str`): The title in the header (e.g., "Deployment status").
        - steps (`list[dict[str, Any]]`): The steps in order, each a dict with the keys "label" (required: what the step is),
        "status" (optional: one of the keys of `_VALID_STATUSES`, "pending" by default) and
        "detail" (optional: a short line under the label, such as the time it finished).
        - description (`str`, optional): A sentence under the header that explains what is tracked.
        - icon (`str`, optional): An icon before the title: a bundled icon's name (e.g., "list-ul-solid")
        or the URL of your own SVG icon. See `DesignSystem.resolve_icon()`.
        - action (`Component`, optional): A component shown on the right of the header, usually an outlined `Button`
        (e.g., `Button("Verify", state = "negative")`). A core button is made smaller to fit the header.
        - heading_level (`int`, optional): The heading element of the title: 2 (default), or 1 to 6 to fit the page outline.
        The look stays the same. It must be an `int`, like PHP's strict check.
        - attributes (`dict[str, Any]`, optional): Extra HTML attributes for the `<section>` as name: value
        (e.g., `{"id": "deployment-status"}`). A "class" is added to the component's own classes.
        `True` writes a bare attribute, `False` or `None` leaves it out.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `title` is empty, `steps` is empty, a step has no label, an unknown key or an unknown status,
        `heading_level` is not accepted, `action` is not a component or `icon` names an unknown icon.
        """
        # Validate the arguments before building anything
        if title is None or title == "":
            raise ValueError("A status tracker needs a title")
        if not isinstance(steps, (list, tuple)) or len(steps) == 0:
            raise ValueError("A status tracker needs at least one step")
        if type(heading_level) is not int or heading_level not in self._VALID_HEADING_LEVELS:
            raise ValueError(f"Invalid status tracker heading level: {self._to_string(heading_level)}")
        if action is not None and not isinstance(action, Component):
            raise ValueError("The status tracker action must be a component, such as a Button")


        # Build the steps, counting the finished ones for the progress bar
        done = 0
        has_error = False
        steps_html = ""
        for step in steps:
            steps_html += self._render_step(step)
            # Raises a ValueError on an invalid step
            status = step.get("status")
            status = "pending" if status is None else status
            done += 1 if status == "done" else 0
            has_error = has_error or status == "error"
        total = len(steps)


        # Collect the section's attributes, adding any extra class to the component's own
        all_attributes: dict[str, Any] = {"class": "ekodata-status-tracker" + (" error" if has_error else "")}
        for key, value in (attributes or {}).items():
            if key == "class":
                all_attributes["class"] += " " + self._to_string(value)
            else:
                all_attributes[key] = value

        # Build the header: the optional icon, the title and the optional action
        html = "<section" + self._attributes_to_html(all_attributes) + ">"
        html += "<div class=\"ekodata-status-tracker-header\">"
        if icon is not None and icon != "":
            icon_style = "--icon-url: url('" + self._escape_css_string(DesignSystem.resolve_icon(icon)) + "')"
            # Raises a ValueError on an unknown icon name
            html += f"<span class=\"icon\" style=\"{self._escape(icon_style)}\" aria-hidden=\"true\"></span>"
        html += f"<h{heading_level} class=\"ekodata-status-tracker-title\">{self._escape(title)}</h{heading_level}>"
        if action is not None:
            html += f"<div class=\"ekodata-status-tracker-action\">{action}</div>"
        html += "</div>"

        # Build the body: the optional description, the steps and the progress bar
        html += "<div class=\"ekodata-status-tracker-body\">"
        if description is not None and description != "":
            html += f"<p class=\"ekodata-status-tracker-description\">{self._escape(description)}</p>"
        html += f"<ol class=\"ekodata-status-tracker-steps\">{steps_html}</ol>"
        html += (
            "<div class=\"ekodata-status-tracker-progress\" role=\"progressbar\" aria-label=\"Progress\""
            + f" aria-valuemin=\"0\" aria-valuemax=\"{total}\" aria-valuenow=\"{done}\" aria-valuetext=\"{done} of {total} steps done\""
            + f" style=\"--ekodata-status-tracker-done: {done}; --ekodata-status-tracker-total: {total}\">"
            + "<div class=\"ekodata-status-tracker-progress-bar\"></div></div>"
        )
        html += "</div>"
        html += "</section>"


        # Store the HTML
        self.html = html


    @staticmethod
    def _render_step(step: Any)-> str:
        """
        Build the HTML of one step: the marker, the label (after its status in words, for screen readers) and the optional detail.

        ### Parameters
        - step (`Any`): The step, as described for the `steps` argument of `__init__()`.

        ### Returns
        - `str`: The step's `<li>`.

        ### Raises
        - `ValueError`: If the step is not a dict, has no label, or has an unknown key or status.
        """
        # Validate the step
        label = step.get("label") if isinstance(step, dict) else None
        if label is None or label == "":
            raise ValueError("Every status tracker step needs a label")
        for key in step:
            if key not in StatusTracker._VALID_STEP_KEYS:
                raise ValueError(f"Unknown status tracker step key: {StatusTracker._to_string(key)}")
        status = step.get("status")
        status = "pending" if status is None else status
        if not isinstance(status, str) or status not in StatusTracker._VALID_STATUSES:
            raise ValueError(f"Invalid status tracker step status: {StatusTracker._to_string(status)}")
        detail = step.get("detail")


        # "done" and "error" are classes, the step in progress is marked with aria-current
        css_class = "ekodata-status-tracker-step"
        if status in ("done", "error"):
            css_class += f" {status}"
        current = " aria-current=\"step\"" if status == "active" else ""

        html = f"<li class=\"{css_class}\"{current}>"
        html += "<span class=\"ekodata-status-tracker-marker\" aria-hidden=\"true\"></span>"
        html += "<span class=\"ekodata-status-tracker-text\">"
        html += (
            "<span class=\"ekodata-status-tracker-label\">"
            + f"<span class=\"ekodata-status-tracker-status\">{StatusTracker._VALID_STATUSES[status]}: </span>"
            + StatusTracker._escape(label) + "</span>"
        )
        if detail is not None and detail != "":
            html += f"<span class=\"ekodata-status-tracker-detail\">{StatusTracker._escape(detail)}</span>"
        html += "</span>"
        html += "</li>"
        return html


    @staticmethod
    def _attributes_to_html(attributes: dict[str, Any])-> str:
        """
        Turn name: value pairs into an escaped attribute string with a leading space.

        ### Parameters
        - attributes (`dict[str, Any]`): The attributes as name: value.
        `True` writes a bare attribute (e.g., ` hidden`), `False` and `None` are skipped.

        ### Returns
        - `str`: The attributes as HTML (e.g., ` class="ekodata-status-tracker" id="deployment-status"`).
        """
        html = ""
        for key, value in attributes.items():
            if value is None or value is False:
                continue
            html += " " + StatusTracker._escape(key)
            if value is not True:
                html += f"=\"{StatusTracker._escape(value)}\""
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
            StatusTracker._to_string(value)
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
            StatusTracker._to_string(value)
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
