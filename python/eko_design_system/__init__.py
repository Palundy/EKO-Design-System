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
"""
from .component import Component
from .design_system import DesignSystem
from .button import Button
from .section_heading import SectionHeading
from .spec_table import SpecTable


__all__ = ["Component", "DesignSystem", "Button", "SectionHeading", "SpecTable"]
