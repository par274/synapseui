import React, { createContext, useContext, useMemo } from "react";

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

export const useTranslations = () => useContext(TranslationsContext);