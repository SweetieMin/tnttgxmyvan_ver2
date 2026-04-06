const sidebarScrollStorageKey = 'app-sidebar-scroll-top';
const sidebarScrollSelector = '[data-app-sidebar-scroll]';

const getSidebarScrollElement = () => document.querySelector(sidebarScrollSelector);

const persistSidebarScrollPosition = () => {
    const sidebarScrollElement = getSidebarScrollElement();

    if (! sidebarScrollElement) {
        return;
    }

    sessionStorage.setItem(sidebarScrollStorageKey, String(sidebarScrollElement.scrollTop));
};

const restoreSidebarScrollPosition = () => {
    const sidebarScrollElement = getSidebarScrollElement();

    if (! sidebarScrollElement) {
        return;
    }

    const storedScrollTop = sessionStorage.getItem(sidebarScrollStorageKey);

    if (storedScrollTop === null) {
        return;
    }

    const scrollTop = Number.parseInt(storedScrollTop, 10);

    window.requestAnimationFrame(() => {
        sidebarScrollElement.scrollTop = Number.isNaN(scrollTop) ? 0 : scrollTop;
    });
};

document.addEventListener('DOMContentLoaded', restoreSidebarScrollPosition);
document.addEventListener('livewire:navigating', persistSidebarScrollPosition);
document.addEventListener('livewire:navigated', restoreSidebarScrollPosition);
document.addEventListener('scroll', (event) => {
    if (! (event.target instanceof HTMLElement)) {
        return;
    }

    if (! event.target.matches(sidebarScrollSelector)) {
        return;
    }

    persistSidebarScrollPosition();
}, {
    capture: true,
    passive: true,
});
