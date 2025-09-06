import { useEffect } from "react";

import { Tooltip } from "bootstrap";

export default function SidebarComponent() {
    useEffect(() => {
        const btn = document.querySelector('[js-ref="side-toggle"]');
        const sidebar = document.querySelector('.side');
        let tooltipList = [];

        const initTooltips = () => {
            tooltipList.forEach(t => t.dispose());
            tooltipList = [];

            if (sidebar.classList.contains('collapsed')) {
                const tooltipTriggerList = sidebar.querySelectorAll('[js-tooltip="rule"]');
                tooltipList = [...tooltipTriggerList].map(el => new Tooltip(el, {
                    animation: false,
                    placement: 'right',
                    title: () => {
                        const label = el.querySelector('.label');
                        return label ? label.textContent.trim() : '';
                    },
                    offset: [0, 15]
                }));
            }
        };

        const saved = localStorage.getItem("sidebar-collapsed") === "true";
        if (saved) {
            sidebar.classList.add("collapsed");
            sidebar.classList.remove("expanded");
        }

        initTooltips();

        if (!btn || !sidebar) return;

        const toggle = (e) => {
            e.preventDefault();

            sidebar.classList.add('ready');
            sidebar.classList.toggle("collapsed");
            sidebar.classList.toggle("expanded");

            const isCollapsed = sidebar.classList.contains("collapsed");
            localStorage.setItem("sidebar-collapsed", isCollapsed);

            initTooltips();
        };

        btn.addEventListener("click", toggle);
        return () => btn.removeEventListener("click", toggle);
    }, []);

    return null;
}