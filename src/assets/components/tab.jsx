import { useEffect } from "react";

export default function TabComponent() {
    useEffect(() => {
        const tabs = document.querySelectorAll('[js-ref="tab"]');

        tabs.forEach((btn) => {
            const tabHrefClass = btn.getAttribute('js-tab-href');
            const tabHref = document.querySelector(`.${tabHrefClass}`);

            const openTab = (e) => {
                e.preventDefault();

                tabs.forEach(el => el.parentNode.classList.remove('active'));

                const activeTabs = document.querySelector('.main').querySelectorAll('.active');
                activeTabs.forEach(el => {
                    el.classList.add('d-none');
                    el.classList.remove('active');
                });

                btn.parentNode.classList.add('active');
                tabHref?.classList.add('active');
                tabHref?.classList.remove('d-none');
            };

            btn.addEventListener("click", openTab);
        });

        return () => {
            tabs.forEach((btn) => {
                btn.removeEventListener("click", openTab);
            });
        };
    }, []);

    return null;
}