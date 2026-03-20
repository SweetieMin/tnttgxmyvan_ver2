const isTypingTarget = (target) => {
    if (!(target instanceof HTMLElement)) {
        return false;
    }

    return (
        target.isContentEditable ||
        ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) ||
        target.closest('[contenteditable="true"]')
    );
};

const getModifierKey = () => {
    const platform =
        navigator.userAgentData?.platform ||
        navigator.platform ||
        navigator.userAgent ||
        '';

    return /Mac|iPhone|iPad|iPod/i.test(platform) ? 'metaKey' : 'ctrlKey';
};

if (!window.__fluxSidebarShortcutBound) {
    window.__fluxSidebarShortcutBound = true;

    document.addEventListener('keydown', (event) => {
        if (isTypingTarget(event.target) || event.altKey || event.shiftKey) {
            return;
        }

        if (!event[getModifierKey()] || event.key.toLowerCase() !== 'b') {
            return;
        }

        const sidebarCollapse =
            document.querySelector('[data-flux-sidebar-collapse] button') ||
            document.querySelector('[data-flux-sidebar-toggle]') ||
            document.querySelector('[data-flux-sidebar-collapse]');

        if (!sidebarCollapse) {
            return;
        }

        event.preventDefault();
        sidebarCollapse.click();
    }, true);
}
