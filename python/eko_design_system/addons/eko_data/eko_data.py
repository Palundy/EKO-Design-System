from eko_design_system.addon import Addon


class EkoData(Addon):
    """
    `EkoData` describes the EKO Data addon: the components that only the EKO Data application needs,
    such as `StatusTracker`. Its CSS lives in `assets/addons/eko-data/`, and its class names and tokens use the `ekodata-` prefix.
    Import it with `from eko_design_system.addons.eko_data import EkoData`.

    Switch it on once, before the page head is rendered:
    ```python
    from eko_design_system import DesignSystem
    from eko_design_system.addons.eko_data import EkoData

    DesignSystem.enable_addon(EkoData)
    ```
    With Flask, pass it to the extension instead: `EkoDesignSystem(app, addons = [EkoData])`.

    The Python counterpart of the PHP `EKO\\DesignSystem\\Addons\\EkoData\\EkoData`.
    """


    NAME: str = "eko-data"
    """The addon's name: its folder in `assets/addons/`, its CSS sub-layer and the prefix of its icon names."""

    STYLESHEETS: tuple[str, ...] = (
        "css/eko-data.css",
    )
    """The addon's stylesheet, linked after the core stylesheet."""

    SCRIPTS: tuple[str, ...] = ()
    """The addon needs no scripts yet."""
