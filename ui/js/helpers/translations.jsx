import PropTypes from "prop-types";

import { createContext, useContext, useMemo } from "react";

const TranslationsContext = createContext({});

export const TranslationsProvider = ({ children }) => {
    const translations = useMemo(
        () => JSON.parse(window.app.js_translations || '{}'),
        []
    );

    return (
        <TranslationsContext.Provider value={translations}>
            {children}
        </TranslationsContext.Provider>
    );
};

TranslationsProvider.propTypes = {
    children: PropTypes.node.isRequired,
};

export const useTranslations = () => useContext(TranslationsContext);