from eko_design_system.addon import Addon


class Icf02(Addon):
    """
    `Icf02` describes the ICF-02 addon: the components that only the ICF-02 application (indoor pyranometer calibrations) needs,
    such as `Sidebar`. Its CSS lives in `assets/addons/icf-02/`, and its class names and tokens use the `icf02-` prefix.
    Import it with `from eko_design_system.addons.icf_02 import Icf02`.

    Switch it on once, before the page head is rendered:
    ```python
    from eko_design_system import DesignSystem
    from eko_design_system.addons.icf_02 import Icf02

    DesignSystem.enable_addon(Icf02)
    ```
    With Flask, pass it to the extension instead: `EkoDesignSystem(app, addons = [Icf02])`.

    The Python counterpart of the PHP `EKO\\DesignSystem\\Addons\\Icf02\\Icf02`.
    """


    NAME: str = "icf-02"
    """The addon's name: its folder in `assets/addons/`, its CSS sub-layer and the prefix of its icon names."""

    STYLESHEETS: tuple[str, ...] = (
        "css/icf-02.css",
    )
    """The addon's stylesheet, linked after the core stylesheet."""

    SCRIPTS: tuple[str, ...] = (
        "js/pincode.js",
    )
    """The addon's scripts, loaded after the core scripts: the pincode's keypad, keyboard input and animations."""
