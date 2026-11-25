document.querySelectorAll(".dropdown select").forEach((select) => {
    const dropdown = select.closest(".dropdown");
    const label = select.labels[0];
    const trigger = document.createElement("button");
    const value = document.createElement("span");
    const icon = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
    const menu = document.createElement("div");
    const options = Array.from(select.options).filter((option) => !(option.disabled && option.value === ""));
    const items = [];
    let activeIndex = -1;

    trigger.type = "button";
    trigger.id = `${select.id}-trigger`;
    trigger.className = "field-input dropdown-trigger";
    trigger.setAttribute("role", "combobox");
    trigger.setAttribute("aria-expanded", "false");
    trigger.setAttribute("aria-haspopup", "listbox");
    trigger.setAttribute("aria-controls", `${select.id}-options`);
    if (select.required) trigger.setAttribute("aria-required", "true");
    if (select.hasAttribute("aria-describedby")) {
        trigger.setAttribute("aria-describedby", select.getAttribute("aria-describedby"));
    }
    if (label) {
        label.htmlFor = trigger.id;
        if (!label.id) label.id = `${select.id}-label`;
        trigger.setAttribute("aria-labelledby", label.id);
        menu.setAttribute("aria-labelledby", label.id);
    }

    value.className = "dropdown-value";
    icon.classList.add("dropdown-icon");
    icon.setAttribute("viewBox", "0 0 16 16");
    icon.setAttribute("aria-hidden", "true");
    path.setAttribute("d", "M4 6l4 4 4-4");
    path.setAttribute("fill", "none");
    path.setAttribute("stroke", "currentColor");
    path.setAttribute("stroke-width", "2");
    icon.append(path);
    trigger.append(value, icon);

    menu.id = `${select.id}-options`;
    menu.className = "dropdown-menu";
    menu.setAttribute("role", "listbox");
    menu.hidden = true;

    options.forEach((option, index) => {
        const item = document.createElement("div");
        item.id = `${select.id}-option-${index}`;
        item.className = "dropdown-option";
        item.setAttribute("role", "option");
        item.textContent = option.textContent;
        if (option.disabled || option.parentElement.disabled) {
            item.setAttribute("aria-disabled", "true");
        }
        item.addEventListener("mousedown", (event) => event.preventDefault());
        item.addEventListener("click", () => {
            if (item.getAttribute("aria-disabled") === "true") return;
            choose(index);
        });
        menu.append(item);
        items.push(item);
    });

    function sync() {
        const selected = select.selectedOptions[0];
        value.textContent = selected ? selected.textContent : "";
        value.classList.toggle("is-placeholder", !selected || (selected.disabled && selected.value === ""));
        trigger.disabled = select.disabled;
        items.forEach((item, index) => {
            item.setAttribute("aria-selected", String(options[index].selected));
        });
    }

    function setActive(index) {
        activeIndex = index;
        items.forEach((item, itemIndex) => item.classList.toggle("is-active", itemIndex === index));
        if (index >= 0) {
            trigger.setAttribute("aria-activedescendant", items[index].id);
            items[index].scrollIntoView({ block: "nearest" });
        } else {
            trigger.removeAttribute("aria-activedescendant");
        }
    }

    function close() {
        menu.hidden = true;
        trigger.setAttribute("aria-expanded", "false");
        setActive(-1);
    }

    function open() {
        if (trigger.disabled) return;
        menu.hidden = false;
        trigger.setAttribute("aria-expanded", "true");
        const selectedIndex = options.findIndex((option, index) => option.selected && items[index].getAttribute("aria-disabled") !== "true");
        setActive(selectedIndex >= 0 ? selectedIndex : items.findIndex((item) => item.getAttribute("aria-disabled") !== "true"));
    }

    function choose(index) {
        if (select.selectedIndex !== options[index].index) {
            select.selectedIndex = options[index].index;
            select.dispatchEvent(new Event("input", { bubbles: true }));
            select.dispatchEvent(new Event("change", { bubbles: true }));
        }
        sync();
        close();
        trigger.focus();
    }

    trigger.addEventListener("click", () => {
        if (menu.hidden) open();
        else close();
    });
    trigger.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            if (!menu.hidden) {
                event.preventDefault();
                close();
            }
        } else if (event.key === "Tab") {
            close();
        } else if (event.key === "ArrowDown" || event.key === "ArrowUp" || event.key === "Home" || event.key === "End") {
            event.preventDefault();
            if (menu.hidden) {
                open();
                return;
            }
            const step = event.key === "ArrowUp" || event.key === "End" ? -1 : 1;
            let index = event.key === "Home" ? 0 : event.key === "End" ? items.length - 1 : activeIndex + step;
            while (index >= 0 && index < items.length && items[index].getAttribute("aria-disabled") === "true") index += step;
            if (index >= 0 && index < items.length) setActive(index);
        } else if ((event.key === "Enter" || event.key === " ") && !menu.hidden) {
            event.preventDefault();
            if (activeIndex >= 0) choose(activeIndex);
        }
    });
    document.addEventListener("click", (event) => {
        if (!dropdown.contains(event.target)) close();
    });
    select.addEventListener("change", sync);
    select.addEventListener("invalid", () => trigger.focus());
    if (select.form) select.form.addEventListener("reset", () => setTimeout(sync, 0));

    select.classList.add("dropdown-native");
    select.setAttribute("aria-hidden", "true");
    select.tabIndex = -1;
    dropdown.append(trigger, menu);
    sync();
});
