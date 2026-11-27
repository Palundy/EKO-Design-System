"""
The EKO Instruments design system for Python: components that render the design system's HTML,
with the same classes, arguments and output as the PHP components in `php/`.

The top-level components are imported from the package itself, the others from sub-packages,
like the PHP namespaces:
```python
from eko_design_system import DesignSystem, Button, SectionHeading, SpecTable
from eko_design_system.fields import Input, Textarea, Dropdown
from eko_design_system.cards import ProductCard, ContactCard, ApplicationCard, IndustryCard, ArticleCard
from eko_design_system.navigation import Breadcrumb, NavItem
```

Addons (application-specific components) live in `eko_design_system.addons.<name>`, a namespace package,
and are switched on with `DesignSystem.enable_addon()` (see `Addon`):
```python
from eko_design_system.addons.eko_data import EkoData, StatusTracker
```
"""
from .component import Component
from .addon import Addon
from .design_system import DesignSystem
from .button import Button
from .section_heading import SectionHeading
from .spec_table import SpecTable


__all__ = ["Component", "Addon", "DesignSystem", "Button", "SectionHeading", "SpecTable"]
