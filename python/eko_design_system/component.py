from abc import ABC


class Component(ABC):
    """
    `Component` is the shared base of every design-system component. It is not used directly.

    A component builds its HTML in `__init__` and returns it as a string, without printing anything:
    ```python
    html = str(Input("Email", name = "email"))      # keep it as a string
    page = f"<form>{Input('Email', name = 'email')}</form>"  # use it inside other HTML
    return render_template("contact.html", email_field = Input("Email", name = "email"))
    ```
    In a Jinja template, `{{ email_field }}` prints the component as HTML, also with autoescaping on:
    the component's `__html__()` tells Jinja (and MarkupSafe) that its HTML is already safe,
    so it is not escaped a second time. Everything the caller passes in is escaped by the component itself.
    """


    html: str | None = None
    """The generated HTML. Set by `__init__` of every component."""


    def __str__(self)-> str:
        """
        Return the component's HTML, so the component can be used as a string.

        ### Returns
        - `str`: The generated HTML.
        """
        return self.html or ""


    def __html__(self)-> str:
        """
        Return the component's HTML for Jinja and MarkupSafe, which treat the result as safe HTML.

        ### Returns
        - `str`: The generated HTML.
        """
        return str(self)
