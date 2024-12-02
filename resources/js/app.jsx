import React from 'react';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import '../css/app.css';
const app = document.getElementById('app');

createInertiaApp({
    resolve: name => import(`./Pages/${name}.jsx`), // Dynamically load components from Pages folder
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);  // Render the app
    },
});