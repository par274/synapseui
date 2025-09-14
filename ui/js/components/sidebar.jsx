import { useEffect } from "react";

export default function SidebarComponent() {
    useEffect(() => {
        const btn = document.querySelector('[js-ref="side-toggle"]');
        const sidebar = document.querySelector('.side');
        const saved = localStorage.getItem("sidebar-collapsed") === "true";
        if (saved) {
            sidebar.classList.add("collapsed");
            sidebar.classList.remove("expanded");
        }

        if (!btn || !sidebar) return;

        const toggle = (e) => {
            e.preventDefault();

            sidebar.classList.add('ready');
            sidebar.classList.toggle("collapsed");
            sidebar.classList.toggle("expanded");

            const isCollapsed = sidebar.classList.contains("collapsed");
            localStorage.setItem("sidebar-collapsed", isCollapsed);
        };

        btn.addEventListener("click", toggle);
        return () => btn.removeEventListener("click", toggle);
    }, []);

    return null;
}