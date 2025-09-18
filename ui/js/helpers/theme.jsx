import PropTypes from "prop-types";

import { createContext, useState, useEffect, useContext } from 'react';

const ThemeContext = createContext();

export const ThemeProvider = ({ children }) => {
    const [themeMode, setThemeMode] = useState(() => {
        const themeAttr = document.documentElement.getAttribute('data-bs-theme');
        return themeAttr === 'dark' ? 'dark' : 'light';
    });

    useEffect(() => {
        const observer = new MutationObserver(() => {
            const themeAttr = document.documentElement.getAttribute('data-bs-theme');
            setThemeMode(themeAttr === 'dark' ? 'dark' : 'light');
        });

        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
        return () => observer.disconnect();
    }, []);

    return (
        <ThemeContext.Provider value={themeMode}>
            {children}
        </ThemeContext.Provider>
    );
};

ThemeProvider.propTypes = {
    children: PropTypes.node.isRequired,
};

export const useThemeMode = () => useContext(ThemeContext);