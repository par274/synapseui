import React, { useState, useRef, useEffect } from "react";
import ReactDOM from "react-dom/client";

import "@olton/metroui/lib/metro.all.js";

const Chat = React.lazy(() => import("./components/chat.jsx"));

const roots = new Map();
function mountIfExists(selector, Component) {
    const el = document.querySelector(selector);
    if (!el) return;

    let root = roots.get(selector);
    if (!root) {
        root = ReactDOM.createRoot(el);
        roots.set(selector, root);
    }

    root.render(
        <React.Suspense fallback={<LoadingSkeleton />}>
            <Component />
        </React.Suspense>
    );
}

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

mountIfExists(".chat-root", Chat);