// Lógica global ligera de UI para interacciones compartidas.
document.addEventListener('DOMContentLoaded', () => {
    // Busca el formulario de búsqueda del header si existe en la página actual.
    const searchForm = document.querySelector('.search-box');

    if (!searchForm) {
        return;
    }

    // Intercepta el envío para evitar recarga mientras no haya backend de búsqueda.
    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const input = searchForm.querySelector('input');
        const query = input ? input.value.trim() : '';

        if (query) {
            window.alert(`Búsqueda pendiente de conectar con backend: ${query}`);
        }
    });
});
