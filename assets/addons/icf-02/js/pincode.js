/*
 * Pincode (ICF-02 addon): makes every `.icf02-pincode` work. See assets/addons/icf-02/css/components/pincode.css for the markup.
 *
 * Input:
 * - Click or tap a key on the keypad.
 * - Or type on the keyboard: the number keys and the numpad enter digits, Backspace and Delete remove the last one,
 *   Escape clears them and Enter submits. The keypad key that matches looks pressed while its key is held down.
 *   Typing goes to the pincode that holds the focus, otherwise to the first pincode on screen that is not unlocked,
 *   and never while the focus is in another text field.
 *
 * Checking the pincode is up to the app. When a complete pincode is submitted, the card shows its checking state
 * and fires `icf02-pincode-submit` (it bubbles). The app checks the pincode and answers with `unlock()` or `reject()`:
 *
 *     document.addEventListener("icf02-pincode-submit", async (event) => {
 *         const response = await fetch("/api/system/login", { method: "POST", body: JSON.stringify({ pincode: event.detail.pincode }) });
 *         (await response.json()).succes ? event.detail.unlock() : event.detail.reject();
 *     });
 *
 * When the unlock animation has finished, the card fires `icf02-pincode-unlocked`, the moment to close it or go on.
 * The same methods are on the element as `element.icf02Pincode`: `unlock(message)`, `reject(message)`, `reset()`,
 * `submit()` and `value`. The messages default to the card's `data-unlocked-message` and `data-error-message`.
 */
(() => {
    const instances = [];
    const heldKeys = new Map();
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");


    /** Reads a duration token such as `--icf02-pincode-shake-duration: 420ms` from the card, in milliseconds. */
    const duration = (root, name) => {
        if (reducedMotion.matches) return 0;
        const value = getComputedStyle(root).getPropertyValue(name).trim();
        const number = parseFloat(value);
        if (Number.isNaN(number)) return 0;
        return value.endsWith("ms") ? number : number * 1000;
    };


    /** Turns a keyboard event into a keypad key: "0" to "9", "delete", "clear" or "enter", or null for any other key. */
    const keyFor = (event) => {
        if (/^[0-9]$/.test(event.key)) return event.key;
        const numpad = /^Numpad([0-9])$/.exec(event.code);
        if (numpad) return numpad[1];
        if (event.key === "Backspace" || event.key === "Delete") return "delete";
        if (event.key === "Escape") return "clear";
        if (event.key === "Enter") return "enter";
        return null;
    };


    /** Finds the pincode that keyboard input goes to, or null. */
    const targetFor = () => {
        const active = document.activeElement;
        const owner = instances.find((instance) => instance.root.contains(active));
        if (owner) return owner;

        // Typing in another field on the page belongs to that field
        if (active && active.matches("input, textarea, select, [contenteditable]:not([contenteditable='false'])")) return null;

        // Otherwise the first card that is on screen and still takes input
        return instances.find((instance) => {
            const root = instance.root;
            const box = root.getBoundingClientRect();
            const onScreen = root.getClientRects().length > 0 && box.bottom > 0 && box.top < window.innerHeight;
            return onScreen && !root.classList.contains("unlocked") && !root.closest("[inert]");
        }) || null;
    };


    /** Sets up one pincode card and returns its controls. */
    const setup = (root) => {
        const length = Number(root.dataset.length) || 4;
        const dots = Array.from(root.querySelectorAll(".icf02-pincode-dot"));
        const dotsGroup = root.querySelector(".icf02-pincode-dots");
        const message = root.querySelector(".icf02-pincode-message");
        const keys = Array.from(root.querySelectorAll(".icf02-pincode-key"));
        const messages = {
            locked: root.dataset.message || "",
            error: root.dataset.errorMessage || "",
            unlocked: root.dataset.unlockedMessage || "",
        };
        let value = "";
        let clearTimer = null;
        let unlockTimer = null;


        // Shows how many digits are entered, as dots and for screen readers
        const render = (filled = value.length) => {
            dots.forEach((dot, index) => dot.classList.toggle("filled", index < filled));
            dotsGroup.setAttribute("aria-label", `${filled} of ${length} digits entered`);
        };

        // Switches between the locked, checking, error and unlocked states
        const setState = (state, text) => {
            root.classList.toggle("checking", state === "checking");
            root.classList.toggle("error", state === "error");
            root.classList.toggle("unlocked", state === "unlocked");
            root.setAttribute("aria-busy", String(state === "checking"));
            if (text !== undefined) message.textContent = text;
            keys.forEach((key) => {
                key.disabled = state === "checking" || state === "unlocked";
            });
        };

        const isBusy = () => root.classList.contains("checking") || root.classList.contains("unlocked");


        const reset = () => {
            clearTimeout(clearTimer);
            clearTimeout(unlockTimer);
            value = "";
            setState("locked", messages.locked);
            render();
        };

        const unlock = (text) => {
            clearTimeout(clearTimer);
            setState("unlocked", text ?? messages.unlocked);
            render(length);
            // Tell the app once the shackle has opened and the ring has faded
            const wait = Math.max(duration(root, "--icf02-pincode-unlock-duration"), duration(root, "--icf02-pincode-ring-duration"));
            unlockTimer = setTimeout(() => {
                root.dispatchEvent(new CustomEvent("icf02-pincode-unlocked", { bubbles: true }));
            }, wait);
        };

        const reject = (text) => {
            clearTimeout(clearTimer);
            // Restart the shake, also when the previous attempt was wrong too
            root.classList.remove("error");
            void root.offsetWidth;
            setState("error", text ?? messages.error);
            render(length);
            // Empty the dots once the shake is over; the error stays until the next key
            clearTimer = setTimeout(() => {
                value = "";
                render();
            }, duration(root, "--icf02-pincode-shake-duration"));
        };

        const submit = () => {
            if (isBusy() || value.length < length) return;
            const pincode = value;
            setState("checking");
            render(length);
            root.dispatchEvent(new CustomEvent("icf02-pincode-submit", {
                bubbles: true,
                detail: { pincode, unlock, reject, reset },
            }));
        };

        // Handles one keypad key: a digit, "delete", "clear" or "enter"
        const press = (key) => {
            if (isBusy()) return;
            if (key === "enter") {
                submit();
                return;
            }

            // The first key after a wrong pincode starts over
            if (root.classList.contains("error")) {
                clearTimeout(clearTimer);
                value = "";
                setState("locked", messages.locked);
            }

            if (/^[0-9]$/.test(key)) {
                if (value.length < length) value += key;
            } else if (key === "delete") {
                value = value.slice(0, -1);
            } else if (key === "clear") {
                value = "";
            }
            render();
        };


        keys.forEach((key) => key.addEventListener("click", (event) => {
            // A mouse or touch click (detail > 0) gives the focus back, so Enter on the keyboard submits
            // instead of pressing the last clicked key again. Keys reached with Tab keep their focus
            if (event.detail > 0) key.blur();
            press(key.dataset.key);
        }));

        // A card rendered as error or unlocked keeps its look until it is used; a locked card starts empty
        if (!root.classList.contains("error") && !root.classList.contains("unlocked")) render();
        if (root.classList.contains("unlocked")) keys.forEach((key) => { key.disabled = true; });

        const instance = { root, press, submit, unlock, reject, reset };
        root.icf02Pincode = {
            get value() { return value; },
            submit,
            unlock,
            reject,
            reset,
        };
        return instance;
    };


    document.querySelectorAll(".icf02-pincode").forEach((root) => instances.push(setup(root)));
    if (instances.length === 0) return;


    // Keyboard input, shared by all cards on the page
    document.addEventListener("keydown", (event) => {
        if (event.ctrlKey || event.metaKey || event.altKey) return;
        const key = keyFor(event);
        if (!key) return;
        const instance = targetFor();
        if (!instance) return;

        // Enter on a keypad key reached with Tab presses that key, as for any button
        // (a key clicked with the mouse gives its focus back, so after clicking, Enter submits)
        if (key === "enter" && document.activeElement && document.activeElement.closest(".icf02-pincode-key")) return;

        event.preventDefault();
        // Holding a digit down types it once; holding Backspace keeps deleting
        if (!event.repeat || key === "delete") instance.press(key);

        // Show the matching keypad key pressed while the key is held down
        const button = instance.root.querySelector(`.icf02-pincode-key[data-key="${key}"]`);
        if (button && !button.disabled) {
            button.classList.add("pressed");
            heldKeys.set(event.code, button);
        }
    });

    document.addEventListener("keyup", (event) => {
        const button = heldKeys.get(event.code);
        if (!button) return;
        button.classList.remove("pressed");
        heldKeys.delete(event.code);
    });

    // A key released outside the window never sends keyup
    window.addEventListener("blur", () => {
        heldKeys.forEach((button) => button.classList.remove("pressed"));
        heldKeys.clear();
    });
})();
