export const disablePlaceholderLinks = () => {
    document.querySelectorAll('[data-placeholder-link]').forEach((link) => {
        link.addEventListener('click', (event) => event.preventDefault());
    });
};
