/* ============================================================
   CONFIG (read from window)
============================================================ */
const updateUrl = window.KANBAN_UPDATE_URL;
const columnOrderUrl = window.KANBAN_COLUMN_ORDER_URL;
const CSRF = window.KANBAN_CSRF;

/* ============================================================
   SHARED STATE
============================================================ */
let draggedTask = null;
let draggedColumn = null;

/* ============================================================
   TASK DRAG HANDLER
============================================================ */
function attachTaskDrag(task) {
    if (!task) return;

    task.dataset.id = task.getAttribute("data-id");

    let moved = false;

    task.addEventListener("mousedown", () => moved = false);
    task.addEventListener("mousemove", () => moved = true);

    task.addEventListener("click", () => {
        if (!moved) openTaskDetails(task.dataset.id);
    });

    task.setAttribute("draggable", "true");

    task.addEventListener("dragstart", e => {
        e.stopPropagation();
        draggedTask = task;

        draggedTask.dataset.id = task.getAttribute("data-id") || task.dataset.id;

        e.dataTransfer.setData("text/plain", draggedTask.dataset.id);
        task.classList.add("dragging");
    });

    task.addEventListener("dragend", () => {

    task.classList.remove("dragging");

    if (!draggedTask) return;

    const column = draggedTask.closest(".kanban-column");
    if (!column) return;

    const zone = column.querySelector(".kanban-tasks-dropzone");
    if (!zone) return;

    const id = draggedTask.dataset.id;
    const status = column.dataset.status;
    const boardId = column.dataset.board;

    const position = [...zone.querySelectorAll(".task-item")]
        .indexOf(draggedTask);

    fetch(updateUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
            "X-CSRF-Token": CSRF,
            "X-Requested-With": "XMLHttpRequest"
        },
        body: new URLSearchParams({
            id,
            status,
            position,
            board_id: boardId
        })
    });

    draggedTask = null;
});

}

document.querySelectorAll(".task-item").forEach(attachTaskDrag);
window.attachTaskDrag = attachTaskDrag;

/* ============================================================
   COLUMN DRAGGING — FIXED
============================================================ */
document.querySelectorAll(".kanban-column").forEach(col => {

    col.draggable = true;

    col.addEventListener("dragstart", e => {

        if (e.target.closest(".task-item")) {
            e.preventDefault();
            return;
        }

        draggedColumn = col;
        col.classList.add("dragging");
    });

    col.addEventListener("dragend", () => {
        col.classList.remove("dragging");

        const order = [];
        document.querySelectorAll(".kanban-column").forEach((c, i) => {
            order.push({
                board_id: c.dataset.board,
                status: c.dataset.status,
                position: i
            });
        });

        fetch(columnOrderUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": CSRF,
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ order })
        });

        draggedColumn = null;
    });

    col.addEventListener("dragover", e => {
        e.preventDefault();

        if (!draggedColumn || draggedColumn === col) return;

        const wrapper = document.querySelector(".kanban-wrapper");
        const columns = [...wrapper.children];

        const next = columns.find(el => {
            const rect = el.getBoundingClientRect();
            return e.clientX < rect.left + rect.width / 2;
        });

        if (next)
            wrapper.insertBefore(draggedColumn, next);
        else
            wrapper.appendChild(draggedColumn);
    });
});

/* ============================================================
   TASK DRAG & DROP INTO COLUMNS
============================================================ */
document.querySelectorAll(".kanban-tasks-dropzone").forEach(zone => {

    zone.addEventListener("drop", e => {
    e.preventDefault();
    zone.classList.remove("kanban-column-drop-hover");

    if (!draggedTask) return;

    draggedTask.dataset.id = draggedTask.getAttribute("data-id") || draggedTask.dataset.id;

    zone.appendChild(draggedTask);

    const id = draggedTask.dataset.id;
    const status = zone.closest(".kanban-column").dataset.status;
    const board = zone.closest(".kanban-column").dataset.board;

    const position = [...zone.querySelectorAll(".task-item")].indexOf(draggedTask);

    if (typeof updateColumnCounts === "function") updateColumnCounts();

    fetch(`${updateUrl}?id=${id}`, {   // <-- FIXED URL
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
            "X-CSRF-Token": CSRF,
            "X-Requested-With": "XMLHttpRequest"
        },
        body: new URLSearchParams({
            status,
            position,
            board_id: board
        })
    });
});
});