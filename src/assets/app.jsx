import { TranslationsProvider } from "./translationsContext.jsx";

import React, { lazy, Suspense } from "react";
import ReactDOM from "react-dom/client";

import "bootstrap";
import { Tooltip } from "bootstrap";

const tooltipTriggerList = document.querySelectorAll('[js-tooltip="true"]');
const tooltipList = [...tooltipTriggerList].map(el => new Tooltip(el, {
    animation: false,
    title: () => {
        const label = el.querySelector('.label');
        return label ? label.textContent.trim() : '';
    }
}));

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
                {withSkeleton ? (
                    <Suspense fallback={<LoadingSkeleton />}>
                        <Component />
                    </Suspense>
                ) : (
                    <Component />
                )}
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