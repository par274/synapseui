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

    function mountIfExists(selector, Component, { withSkeleton = false } = {}) {
        const el = document.querySelector(selector);
        if (!el) return;

        let root = roots.get(selector);
        if (!root) {
            root = ReactDOM.createRoot(el);
            roots.set(selector, root);
        }

        const content = (
            <TranslationsProvider>
                <ThemeProvider>
                    {withSkeleton ? (
                        <Suspense fallback={<LoadingSkeleton />}>
                            <Component />
                        </Suspense>
                    ) : (
                        <Component />
                    )}
                </ThemeProvider>
            </TranslationsProvider>
        );

        root.render(content);
    }

    return {
        mountTabComponent: function (TabComponent) {
            mountIfExists('[js-ref="tab"] .js-ref', TabComponent);
        },
        mountChat: function (ChatComponent) {
            mountIfExists('.chat-root', ChatComponent, { withSkeleton: true });
        },
        mountSidebarComponent: function (SidebarComponent) {
            mountIfExists('[js-ref="side-toggle"] .js-ref', SidebarComponent);
        }
    };
})();
AppInit.mountTabComponent(TabComponent);
AppInit.mountChat(ChatComponent);
AppInit.mountSidebarComponent(SidebarComponent);