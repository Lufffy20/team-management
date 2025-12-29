(function () {
    /* ============================================================
       CONFIG (from window)
    ============================================================ */
    const createUrl      = window.KANBAN_CREATE_URL;
    const updateUrl      = window.KANBAN_UPDATE_URL;
    const viewUrl        = window.KANBAN_VIEW_URL;
    const columnOrderUrl = window.KANBAN_COLUMN_ORDER_URL;
    const CSRF           = window.KANBAN_CSRF;

    if (!updateUrl) {
        console.error('KANBAN_UPDATE_URL missing');
    }

    let draggedTask    = null;
    let draggedColumn  = null;
    let isTaskDragging = false;

    /* ============================================================
       TASK DRAG + CLICK (open modal)
    ============================================================ */
    function attachTaskDrag(task) {
        if (!task) return;

        const id = task.dataset.id || task.getAttribute('data-id');
        if (!id) {
            console.warn('Task without data-id, skip', task);
            return;
        }
        task.dataset.id = id;

        task.setAttribute('draggable', 'true');

        let moved = false;
        task.addEventListener('mousedown', () => { moved = false; });
        task.addEventListener('mousemove', () => { moved = true; });

        // Click -> open details (but not when dragged)
        task.addEventListener('click', function (e) {
            if (moved || isTaskDragging) return;
            e.preventDefault();
            openTaskDetails(id);
        });

        task.addEventListener('dragstart', function (e) {
            isTaskDragging = true;
            draggedTask = task;
            task.classList.add('dragging-task');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', id);

            // Column drag se conflict avoid
            e.stopPropagation();
        });

        task.addEventListener('dragend', function () {

    isTaskDragging = false;

    if (!draggedTask) return;

    const column = draggedTask.closest('.kanban-column');
    if (!column) return;

    const status  = column.dataset.status;
    const boardId = column.dataset.board;

    const zone = column.querySelector('.kanban-tasks-dropzone');
    const tasks = Array.from(zone.querySelectorAll('.kanban-task, .task-item'));
    const position = tasks.indexOf(draggedTask);

    updateTaskOnServer(draggedTask.dataset.id, boardId);


    draggedTask.classList.remove('dragging-task');
    draggedTask = null;

    updateAllColumnCounts();
});

    }

    /* ============================================================
       TASK DROPZONES (inside columns)
    ============================================================ */
    function attachDropzones() {
        const dropzones = document.querySelectorAll('.kanban-tasks-dropzone');

        dropzones.forEach(zone => {
            zone.addEventListener('dragover', function (e) {
                if (!draggedTask) return;
                e.preventDefault();

                const afterElement = getDragAfterElement(zone, e.clientY);
                if (!afterElement) {
                    zone.appendChild(draggedTask);
                } else {
                    zone.insertBefore(draggedTask, afterElement);
                }
            });

            zone.addEventListener('drop', function (e) {
                e.preventDefault();
                if (!draggedTask) return;

                const column = zone.closest('.kanban-column');
                if (!column) return;

                const status  = column.dataset.status;
                const boardId = column.dataset.board;

                const tasksInZone = Array.from(
                    zone.querySelectorAll('.kanban-task, .task-item')
                );
                const position = tasksInZone.indexOf(draggedTask);

                updateTaskOnServer(draggedTask.dataset.id, status, position, boardId);
                updateAllColumnCounts();
            });
        });
    }

    // Helper to find element after mouse for smooth sorting
    function getDragAfterElement(container, y) {
        const draggableElements = [
            ...container.querySelectorAll('.kanban-task:not(.dragging-task), .task-item:not(.dragging-task)')
        ];

        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    /* ============================================================
       AJAX: UPDATE TASK STATUS / POSITION
    ============================================================ */
    function updateTaskOnServer(draggedTaskId, boardId) {
    if (!updateUrl || !draggedTask) return;

    // 👉 destination column (jahan task drop hua)
    const column = draggedTask.closest('.kanban-column');
    if (!column) return;

    const status = column.dataset.status; // 🔥 NEW (important)
    const zone   = column.querySelector('.kanban-tasks-dropzone');

    // 👉 destination column ke sab task IDs (new order)
    const taskIds = [];
    zone.querySelectorAll('.kanban-task, .task-item').forEach(el => {
        taskIds.push(el.dataset.id);
    });

    const formData = new FormData();
    formData.append('board_id', boardId);
    formData.append('status', status);          // 🔥 NEW
    formData.append('moved_id', draggedTaskId); // 🔥 NEW

    taskIds.forEach(id => formData.append('tasks[]', id));

    fetch(updateUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': CSRF
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            console.error('Task move/reorder failed', data);
        }
    })
    .catch(err => console.error('Task move/reorder error', err));
}



    /* ============================================================
       BADGE COUNTS
    ============================================================ */
    function updateAllColumnCounts() {
        document.querySelectorAll('.kanban-column').forEach(col => {
            const zone  = col.querySelector('.kanban-tasks-dropzone');
            const badge = col.querySelector('.column-count');
            if (!zone || !badge) return;

            const count = zone.querySelectorAll('.kanban-task, .task-item').length;
            badge.textContent = count;
        });
    }



    /* ============================================================
       COLUMN DRAG (change column order) – separate from tasks
    ============================================================ */
    function attachColumnDrag() {
        const wrapper = document.querySelector('.kanban-wrapper');
        const columns = document.querySelectorAll('.kanban-column');

        if (!wrapper) return;

        columns.forEach(col => {
            col.setAttribute('draggable', 'true');

            col.addEventListener('dragstart', function (e) {
                // Agar task ko drag kar rahe hai to column drag cancel
                if (e.target.closest('.kanban-task, .task-item')) {
                    e.preventDefault();
                    return;
                }

                // Agar already task dragging hai to bhi cancel
                if (isTaskDragging) {
                    e.preventDefault();
                    return;
                }

                draggedColumn = col;
                col.classList.add('dragging-column');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', col.dataset.status);
            });

            col.addEventListener('dragend', function () {
                if (draggedColumn) draggedColumn.classList.remove('dragging-column');
                draggedColumn = null;
            });
        });

        wrapper.addEventListener('dragover', function (e) {
            if (!draggedColumn || isTaskDragging) return;
            e.preventDefault();

            const afterCol = getDragAfterColumn(wrapper, e.clientX);
            if (!afterCol) {
                wrapper.appendChild(draggedColumn);
            } else {
                wrapper.insertBefore(draggedColumn, afterCol);
            }
        });

        wrapper.addEventListener('drop', function (e) {
            if (!draggedColumn || isTaskDragging) return;
            e.preventDefault();
            sendColumnOrder();
        });
    }

    function getDragAfterColumn(container, x) {
        const cols = [...container.querySelectorAll('.kanban-column:not(.dragging-column)')];

        return cols.reduce((closest, col) => {
            const box = col.getBoundingClientRect();
            const offset = x - box.left - box.width / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: col };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function sendColumnOrder() {
        if (!columnOrderUrl) return;

        const wrapper = document.querySelector('.kanban-wrapper');
        if (!wrapper) return;

        const order = [];
        wrapper.querySelectorAll('.kanban-column').forEach((col, index) => {
            order.push({
                board_id: col.dataset.board,
                status: col.dataset.status,
                position: index
            });
        });

        fetch(columnOrderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF
            },
            body: JSON.stringify({ order })
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    console.error('Column order save failed', data);
                }
            })
            .catch(err => console.error('Column order error', err));
    }

    /* ============================================================
       CREATE TASK (modal form)
    ============================================================ */
    function attachCreateForm() {
        const form = document.getElementById('taskCreateForm');
        if (!form || !createUrl) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(form);

            fetch(createUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': CSRF
                },
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Create task failed', data);
                        return;
                    }

                    const status = data.status || 'todo';
                    const html   = data.html;

                    const zone = document.querySelector(
                        '.kanban-column[data-status="' + status + '"] .kanban-tasks-dropzone'
                    );

                    if (zone && html) {
                        const temp = document.createElement('div');
                        temp.innerHTML = html.trim();
                        const newTask = temp.firstElementChild;

                        if (newTask) {
                            zone.appendChild(newTask);
                            attachTaskDrag(newTask);
                            updateAllColumnCounts();
                        }
                    }

                    form.reset();
                    const modalEl = document.getElementById('taskModal');
                    if (modalEl && window.bootstrap) {
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.hide();
                    }
                })
                .catch(err => console.error('Create task error', err));
        });
    }

    /* ============================================================
       INIT
    ============================================================ */
    document.addEventListener('DOMContentLoaded', function () {
        // Attach drag on all existing tasks (supports both class names)
        document
            .querySelectorAll('.kanban-task, .task-item')
            .forEach(attachTaskDrag);

        attachDropzones();
        attachColumnDrag();
        attachCreateForm();
        updateAllColumnCounts();
    });
    
    

})();
