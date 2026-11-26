import re
from typing import Any

from .card import Card


class ContactCard(Card):
    """
    `ContactCard` renders a contact card (`.contact-card`, see assets/css/components/contact-card.css).
    It returns its HTML instead of printing it: use it as a string with `str(ContactCard(...))`,
    or print it in a Jinja template with `{{ card }}`.
    Import it with `from eko_design_system.cards import ContactCard`.

    Every part except the first line is optional and is left out when not given:
    ```html
    <div class="contact-card">
        <div class="card-header">
            <div class="card-image"><img src="{image}" alt="{image_alt}"></div>
            <div class="card-contact-details">
                <div class="card-contact-details-primary">
                    <div class="card-contact-details-first">{first}</div>
                    <div class="card-contact-details-second">{second}</div>
                    <div class="card-contact-details-third">{third}</div>
                </div>
                <div class="card-contact-details-secondary">
                    <div class="card-contact-details-email">{icon} {email}</div>
                    <div class="card-contact-details-phone">{icon} {phone}</div>
                    <div class="card-contact-details-website">{icon} {website}</div>
                </div>
            </div>
        </div>
        <button class="card-button btn negative">{button_icon} {button}</button>
    </div>
    ```

    Examples:
    - `ContactCard("Palau van Helden")`
    - `ContactCard("Palau van Helden", second = "EKO Instruments B.V.", email = "info@eko-instruments.com")`
    - `ContactCard("Sales", phone = "+31 70 3050117", button = "Call us", button_icon = "call-outline")`

    All text and attribute values are HTML-escaped. The generated class names must stay in sync
    with the hand-written contact card in index.html.
    """


    _EMAIL_ICON: str = "envelope-outline"
    """The icon shown before the email address."""

    _PHONE_ICON: str = "call-outline"
    """The icon shown before the phone number."""

    _WEBSITE_ICON: str = "internet-outline"
    """The icon shown before the website."""


    def __init__(
        self,
        first: str,
        second: str | None = None,
        third: str | None = None,
        email: str | None = None,
        phone: str | None = None,
        website: str | None = None,
        image: str | None = None,
        image_alt: str | None = None,
        button: str | None = None,
        button_icon: str | None = None,
        button_attributes: dict[str, Any] | None = None
    )-> None:
        """
        Build the contact card HTML and store it in `html`.
        Pass keyword arguments so the optional parameters can be skipped.

        ### Parameters
        - first (`str`): The first line, shown in bold (usually the person's name).
        - second (`str`, optional): The second line (e.g., the company).
        - third (`str`, optional): The third line (e.g., the job title).
        - email (`str`, optional): The email address. Without it, the email row and its icon are left out.
        - phone (`str`, optional): The phone number. Without it, the phone row and its icon are left out.
        - website (`str`, optional): The website, shown as text (e.g., "www.eko-instruments.com").
        Without it, the website row and its icon are left out.
        - image (`str`, optional): The URL of a photo, shown left of the details. Without it, the card has no image.
        - image_alt (`str`, optional): The image's alt text. Defaults to `first`.
        - button (`str`, optional): The label of the outlined button at the bottom of the card. Without it, the card has no button.
        - button_icon (`str`, optional): The icon shown before the button label: a bundled icon's name
        (e.g., "envelope-outline") or the URL of your own SVG icon. Only used when `button` is given.
        - button_attributes (`dict[str, Any]`, optional): Extra HTML attributes for the button as name: value,
        to hook it up to your page (e.g., `{"id": "contact-palau", "data-contact": "palau"}`).
        `True` writes a bare attribute, `False` or `None` leaves it out. `class` and `type` cannot be changed.

        ### Returns
        - `None`

        ### Raises
        - `ValueError`: If `first` is empty, or `button_icon` names an unknown icon.
        """
        # Validate the arguments before building anything
        if not self._is_filled(first):
            raise ValueError("A contact card needs a first line")


        # Build the header: the optional image next to the details
        html = "<div class=\"contact-card\">"
        html += "<div class=\"card-header\">"
        if self._is_filled(image):
            alt = image_alt if image_alt is not None else first
            html += f"<div class=\"card-image\"><img src=\"{self._escape(image)}\" alt=\"{self._escape(alt)}\"></div>"
        html += "<div class=\"card-contact-details\">"

        # Add the primary details: the first line and the optional second and third lines
        html += "<div class=\"card-contact-details-primary\">"
        html += f"<div class=\"card-contact-details-first\">{self._escape(first)}</div>"
        if self._is_filled(second):
            html += f"<div class=\"card-contact-details-second\">{self._escape(second)}</div>"
        if self._is_filled(third):
            html += f"<div class=\"card-contact-details-third\">{self._escape(third)}</div>"
        html += "</div>"

        # Add the secondary details (email, phone and website), each as an icon and a clickable link.
        # Rows without a value are left out, and so is the whole block when no row has a value.
        secondary_details = [
            (kind, value, icon)
            for kind, value, icon in (
                ("email", email, self._EMAIL_ICON),
                ("phone", phone, self._PHONE_ICON),
                ("website", website, self._WEBSITE_ICON),
            )
            if self._is_filled(value)
        ]
        if secondary_details:
            html += "<div class=\"card-contact-details-secondary\">"
            for kind, value, icon in secondary_details:
                # Websites open in a new tab, `noopener` keeps that page from controlling this one
                is_website = kind == "website"
                link = "<a" + self._attributes_to_html({
                    "class": "link",
                    "href": self._detail_href(kind, value),
                    "target": "_blank" if is_website else None,
                    "rel": "noopener" if is_website else None,
                }) + ">" + self._escape(value) + "</a>"
                html += f"<div class=\"card-contact-details-{kind}\">{self._render_icon(icon)} {link}</div>"
            html += "</div>"
        html += "</div></div>"

        # Add the optional button below the header
        if self._is_filled(button):
            html += self._render_button(button, button_icon, button_attributes or {})
            # Raises a ValueError on an unknown button icon
        html += "</div>"


        # Store the HTML
        self.html = html


    @staticmethod
    def _detail_href(
        kind: str,
        value: str
    )-> str:
        """
        Build the link target of a secondary detail, so the shown text can stay as the caller wrote it.

        - "email": `mailto:` followed by the address.
        - "phone": `tel:` followed by the number without spaces, dashes or brackets.
        For international numbers the "(0)" trunk prefix is dropped (e.g., "+31 (0)6 1234" becomes "tel:+3161234").
        - "website": the URL as given when it starts with "http://" or "https://", otherwise "https://" is put in front.

        ### Parameters
        - kind (`str`): The kind of detail: "email", "phone" or "website".
        - value (`str`): The detail as shown on the card.

        ### Returns
        - `str`: The unescaped link target.
        """
        # Strip the same characters as PHP's trim(), so the link targets stay identical
        value = str(value).strip(" \t\n\r\0\x0b")

        if kind == "email":
            return f"mailto:{value}"
        if kind == "phone":
            if value.startswith("+"):
                value = value.replace("(0)", "")
            return "tel:" + re.sub(r"(?!^\+)[^0-9]", "", value)
        return value if re.match(r"https?://", value, re.IGNORECASE) else f"https://{value}"


    @staticmethod
    def _render_button(
        label: str,
        icon: str | None,
        attributes: dict[str, Any]
    )-> str:
        """
        Build the outlined button at the bottom of the card.

        ### Parameters
        - label (`str`): The button label.
        - icon (`str | None`): A bundled icon's name or the URL of an SVG icon shown before the label, or `None` for no icon.
        - attributes (`dict[str, Any]`): Extra HTML attributes as name: value. `class` and `type` are ignored.

        ### Returns
        - `str`: The button's HTML.

        ### Raises
        - `ValueError`: If `icon` names an unknown icon.
        """
        all_attributes = {
            "type": "button",
            "class": "card-button btn negative",
            **{key: value for key, value in attributes.items() if key not in ("class", "type")},
        }

        content = ContactCard._escape(label)
        if ContactCard._is_filled(icon):
            content = ContactCard._render_icon(icon) + f" {content}"
            # Raises a ValueError on an unknown icon name
        return "<button" + ContactCard._attributes_to_html(all_attributes) + f">{content}</button>"
