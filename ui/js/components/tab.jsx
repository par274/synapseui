import { useEffect } from "react";

export default function TabComponent() {
    useEffect(() => {
        const tabs = document.querySelectorAll('[js-ref="tab"]');
        const main = document.querySelector(".main");

        const openTab = (e, btn, tabHref) => {
            e.preventDefault();

            tabs.forEach(el => el.parentNode.classList.remove("active"));

            const activeTabs = main.querySelectorAll(".active");
            activeTabs.forEach(el => {
                el.classList.add("d-none");
                el.classList.remove("active");
            });

            btn.parentNode.classList.add("active");
            tabHref?.classList.add("active");
            tabHref?.classList.remove("d-none");
        };

        tabs.forEach((btn) => {
            const tabHrefClass = btn.getAttribute("js-tab-href");
            const tabHref = document.querySelector(`.${tabHrefClass}`);

            const handleClick = (e) => openTab(e, btn, tabHref);

            btn.addEventListener("click", handleClick);

            btn._handler = handleClick;
        });

        return () => {
            tabs.forEach((btn) => {
                if (btn._handler) btn.removeEventListener("click", btn._handler);
            });
        };
    }, []);

    return null;
}