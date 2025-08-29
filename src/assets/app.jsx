import React, { lazy, Suspense } from "react";
import ReactDOM from "react-dom/client";

import "@olton/metroui/lib/metro.all.js";

const AppChat = lazy(() => import("./components/chat.jsx"));
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

    function mountIfExists(selector, Component) {
        const el = document.querySelector(selector);
        if (!el) return;

        let root = roots.get(selector);
        if (!root) {
            root = ReactDOM.createRoot(el);
            roots.set(selector, root);
        }

        root.render(
            <Suspense fallback={<LoadingSkeleton />}>
                <Component />
            </Suspense>
        );
    }

    return {
        mountChat: function (ChatComponent) {
            mountIfExists(".chat-root", ChatComponent);
        }
    };
})();
AppInit.mountChat(AppChat);