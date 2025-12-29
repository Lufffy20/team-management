/******************************************************
 🔥 SUBTASK ACTIONS — No Auto Save Trigger + CSRF FIX
******************************************************/

// Get CSRF Token from page
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content 
          || document.getElementById("csrfToken")?.value;

/* ===============================
   ADD SUBTASK
===============================*/
$(document).on('click', '#addSubtaskBtn', function () {

    let title  = $('#newSubtaskInput').val().trim();
    let taskId = $(this).data('task');

    if (!title) return;

    $.post('/task/add-subtask?task_id=' + taskId, {
        title: title,
        _csrf: yii.getCsrfToken()
    }, function (res) {

        if (res.success) {

            // ❌ "No subtasks" text hata do
            $('#subtaskList .no-subtask').remove();

            // ✅ UI me turant add
            $('#subtaskList').append(res.html);

            // ✅ input clear
            $('#newSubtaskInput').val('');
        }
    });
});



/* ===============================
   DELETE SUBTASK
===============================*/
document.addEventListener("click", function(e){
    if(e.target.matches(".delete-subtask")){
        e.preventDefault();
        e.stopPropagation();

        if(!confirm("Delete Subtask?")) return;

        let id=e.target.dataset.id;

        fetch("/task/delete-subtask?id="+id,{
            method:"POST",
            headers:{ "X-CSRF-Token": CSRF }        // 🔥 Required
        })
        .then(()=> e.target.closest(".subtask-item").remove());
    }
});


/* ===============================
   TOGGLE SUBTASK STATUS
===============================*/
document.addEventListener("change", function (e) {

    if (!e.target.matches(".toggle-subtask")) return;

    e.stopPropagation();

    const checkbox = e.target;
    const id       = checkbox.dataset.subtaskId;
    const item     = checkbox.closest(".subtask-item");
    const label    = item.querySelector(".subtask-title");

    fetch("/task/toggle-subtask?id=" + id, {
        method: "POST",
        headers: {
            "X-CSRF-Token": CSRF
        }
    })
    .then(r => r.json())
    .then(res => {

        if (!res.success) {
            // ❌ backend failed → revert checkbox
            checkbox.checked = !checkbox.checked;
            alert("Subtask update failed");
            return;
        }

        // ✅ backend success → UI update
        if (res.is_done == 1) {
            label.classList.add("line-through", "text-muted");
        } else {
            label.classList.remove("line-through", "text-muted");
        }

    })
    .catch(() => {
        // ❌ network error → revert checkbox
        checkbox.checked = !checkbox.checked;
        alert("Network error");
    });
});



/******************************************************
 👉 LIVE ADD NEW ROW UI
******************************************************/
function addSubtaskRow(s){
    document.querySelector(".subtask-list").insertAdjacentHTML("beforeend",`
    <div class="d-flex align-items-center justify-content-between subtask-item mb-2">
        <div class="d-flex align-items-center gap-2">
            <input type="checkbox" class="toggle-subtask" data-subtask-id="${s.id}">
            <span>${s.title}</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger delete-subtask" data-id="${s.id}">✖</button>
    </div>
    `);
}

/* =======================
   GLOBAL TOAST FUNCTION
======================= */
window.showToast = function(msg){
    let toast = document.createElement("div");
    toast.className="toast-popup";
    toast.innerText=msg;

    document.body.appendChild(toast);

    setTimeout(()=> toast.classList.add("show"),10);  
    setTimeout(()=> toast.classList.remove("show"),2000);
    setTimeout(()=> toast.remove(),2600);
};
