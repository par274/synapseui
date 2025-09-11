import { TranslationsProvider } from "./translationsContext.jsx";
import { ThemeProvider } from './themeContext.jsx';

import React, { lazy, Suspense } from "react";
import ReactDOM from "react-dom/client";

import "bootstrap";
import { Tooltip } from "bootstrap";

(() => {
    const App_ThemeSwitcher = (function () {
        const htmlEl = document.documentElement;
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

        function setTheme() {
            if (prefersDark.matches) {
                htmlEl.setAttribute('data-bs-theme', 'dark');
            } else {
                htmlEl.setAttribute('data-bs-theme', 'light');
            }
        }

        setTheme();

        prefersDark.addEventListener('change', setTheme);
    })();

    const App_TooltipInit = (function () {
        const tooltipTriggerList = document.querySelectorAll('[js-tooltip]');

        function initTooltip(el) {
            if (el._tooltip) return el._tooltip;

            const label = el.querySelector('.label');
            let isHTML = false;
            if (label) {
                isHTML = label.innerHTML.trim() !== label.textContent.trim();
            }
            const tooltip = new Tooltip(el, {
                html: isHTML,
                animation: false,
                title: () => {
                    if (!label) return '';

                    const allowedTags = ['b', 'i', 'strong', 'em', 'span'];
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = label.innerHTML;

                    tempDiv.querySelectorAll('*').forEach(node => {
                        if (!allowedTags.includes(node.tagName.toLowerCase())) {
                            node.replaceWith(document.createTextNode(node.textContent));
                        }
                    });

                    return tempDiv.innerHTML;
                },
                placement: () => el.getAttribute('js-tooltip-placement') ?? 'auto',
                offset: () => {
                    if (el.hasAttribute('js-tooltip-offset')) {
                        try {
                            return JSON.parse(el.getAttribute('js-tooltip-offset'));
                        } catch (e) {
                            return [0, 6];
                        }
                    }
                    return [0, 6];
                }
            });

            el.addEventListener('click', () => tooltip.hide());
            el._tooltip = tooltip;
            return tooltip;
        }

        function destroyTooltip(el) {
            if (el._tooltip) {
                el._tooltip.dispose();
                delete el._tooltip;
            }
        }

        tooltipTriggerList.forEach(el => {
            const rule = el.getAttribute('js-tooltip-rule');

            if (!rule) {
                initTooltip(el);
            } else {
                const observer = new MutationObserver(() => {
                    if (el.closest(`.side.${rule}`)) {
                        initTooltip(el);
                    } else {
                        destroyTooltip(el);
                    }
                });

                const parent = el.closest('.side') || el.parentElement;
                observer.observe(parent, { attributes: true, subtree: true, attributeFilter: ['class'] });

                if (el.closest(`.side.${rule}`)) {
                    initTooltip(el);
                }
            }
        });
    })();
})();

const TabComponent = lazy(() => import("./components/tab.jsx"));
const ChatComponent = lazy(() => import("./components/chat.jsx"));
const SidebarComponent = lazy(() => import("./components/sidebar.jsx"));
const AppInit = (function () {
    const roots = new Map();

    function LoadingSkeleton() {
        return (
            <div class="skeleton-animation">
                <div class="skeleton-effect"></div>
                <div class="skeleton-effect lv1"></div>
                <div class="skeleton-effect lv2"></div>
                <div class="skeleton-effect lv3"></div>
                <div class="skeleton-effect lv4"></div>
            </div>
        );
    }

    /**
     * Mounts a React component into the DOM.
     * @param {string|HTMLElement|null} selectorOrEl - CSS selector or HTMLElement to mount into
     * @param {React.Component} Component - React component
     * @param {Object} options
     * @param {boolean} options.withSkeleton - Use Suspense skeleton fallback
     * @param {boolean} options.useHeadless - Create a div if selector doesn't exist
     */
    function mountIfExists(selectorOrEl, Component, { withSkeleton = false, useHeadless = false } = {}) {
        let el;

        if (typeof selectorOrEl === 'string') {
            el = document.querySelector(selectorOrEl);
        } else if (selectorOrEl instanceof HTMLElement) {
            el = selectorOrEl;
        }

        if (!el) return null;

        if (useHeadless) {
            let container = document.querySelector('.react-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'react-container';
                document.body.appendChild(container);
            }

            el = document.createElement('div');
            el.dataset.headless = "true";
            container.appendChild(el);
        }

        let root = roots.get(el);
        if (!root) {
            root = ReactDOM.createRoot(el);
            roots.set(el, root);
        }

        let props = { ...el.dataset };
        if (props.config) {
            try { props = { ...props, ...JSON.parse(props.config) }; } catch (e) { console.warn("Invalid JSON in data-config", e); }
        }

        const content = (
            <TranslationsProvider>
                <ThemeProvider>
                    {withSkeleton ? (
                        <Suspense fallback={<LoadingSkeleton />}>
                            <Component {...props} />
                        </Suspense>
                    ) : (
                        <Component {...props} />
                    )}
                </ThemeProvider>
            </TranslationsProvider>
        );

        root.render(content);

        return el;
    }

    /**
     * Unmount a React component and clean up
     * @param {string|HTMLElement} selectorOrEl
     */
    function unmount(selectorOrEl) {
        let el;
        if (typeof selectorOrEl === 'string') {
            el = document.querySelector(selectorOrEl);
        } else if (selectorOrEl instanceof HTMLElement) {
            el = selectorOrEl;
        }

        if (!el) return;

        const root = roots.get(el);
        if (root) {
            root.unmount();
            roots.delete(el);

            if (el.dataset.headless === "true") {
                el.remove();
            }
        }
    }

    return {
        mountIfExists,
        unmount
    };
})();
AppInit.mountIfExists('[js-ref="tab"]', TabComponent, { withSkeleton: false, useHeadless: true });
AppInit.mountIfExists('.chat-root', ChatComponent, { withSkeleton: true });
AppInit.mountIfExists('[js-ref="side-toggle"]', SidebarComponent, { withSkeleton: false, useHeadless: true });