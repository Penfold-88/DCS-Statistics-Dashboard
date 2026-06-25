(function () {
    const container = document.getElementById('menu-manager-items');
    if (!container) return;
    let dragged = null;

    function renumber() {
        container.querySelectorAll('.menu-manager-item').forEach((item, index) => {
            item.querySelectorAll('[data-field]').forEach(field => {
                field.name = `items[${index}][${field.dataset.field}]`;
            });
        });
    }

    container.querySelectorAll('.menu-manager-item').forEach(item => {
        item.addEventListener('dragstart', () => {
            dragged = item;
            item.classList.add('dragging');
        });
        item.addEventListener('dragend', () => {
            item.classList.remove('dragging');
            dragged = null;
            renumber();
        });
        item.addEventListener('dragover', event => {
            event.preventDefault();
            if (!dragged || dragged === item) return;
            const box = item.getBoundingClientRect();
            container.insertBefore(dragged, event.clientY < box.top + box.height / 2 ? item : item.nextSibling);
        });
    });
}());
