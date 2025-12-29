/******************************************************
 🔥 REALTIME AUTO SAVE (Trello Style)
******************************************************/

let typeTimer;
const SAVE_DELAY = 800; // .8 sec delay after user stops typing


function autoSaveTask(){

    let form = $("#taskUpdateForm");

    $.ajax({
        url: form.attr("action"),
        method: "POST",
        data: form.serialize(),
        dataType:"json",

        success:function(res){
            console.log("💾 AUTO SAVED", res);

            if(res.success && res.card && res.id){

                // 🔥 Update Task Card UI LIVE (No Refresh Needed)
                let card = $("#taskCard"+res.id);
                if(card.length){
                    card.replaceWith(res.card);
                }

                // 🔥 If status changed, move to new column instantly
                if(res.status){
                    let targetColumn = $(`.kanban-column[data-status='${res.status}'] .kanban-tasks-dropzone`);
                    $(`#taskCard${res.id}`).appendTo(targetColumn);
                }

                updateColumnCount();

                // Toast safe check
                if(typeof showToast === "function"){
                    showToast("Saved ✔");
                }
            }
        }
    });
}



/******************************************************
 🔥 Auto-Save on Task Input (except subtasks)
******************************************************/

$(document).on("input change",
    "#taskUpdateForm input, #taskUpdateForm textarea, #taskUpdateForm select",
function(e){

    // ⛔ HARD STOP autosave for delete / upload actions
    if (
        $(e.target).hasClass("no-save-trigger") ||
        $(e.target).closest(".no-save-trigger").length ||
        $(e.target).closest(".task-image-preview").length ||
        $(e.target).closest(".subtask-item").length ||
        $(e.target).is("#newSubtaskInput") ||
        $(e.target).is("#addSubtaskBtn")
    ) {
        return;
    }

    clearTimeout(typeTimer);
    typeTimer = setTimeout(autoSaveTask, SAVE_DELAY);
});



/******************************************************
 🔥 Manual Save (if you ever add Save button)
******************************************************/

$(document).on("submit", "#taskUpdateForm", function(e){
    e.preventDefault();
    autoSaveTask();
    $('#editTaskModal').modal('hide');
});



/******************************************************
 🔥 DELETE TASK
******************************************************/

function deleteTask(id){

    if(!confirm("Delete permanently?")) return;

    $.ajax({
        url:"/task/delete-ajax?id="+id,
        type:"POST",
        dataType:"json",
        success:function(res){
            if(res.success){

                $(`#taskCard${id}`).fadeOut(300,function(){ $(this).remove(); });
                $('#editTaskModal').modal('hide');
                updateColumnCount();

                if(typeof showToast==="function"){
                    showToast("Task Deleted 🗑");
                }
            }
        }
    });
}




/******************************************************
 🔥 UPDATE COLUMN COUNT
******************************************************/
function updateColumnCount(){
    $(".kanban-column").each(function(){
        let c=$(this).find(".kanban-task").length;
        $(this).find(".column-count").text(c);
    });
}

// 🔥 DELETE TASK IMAGE (GLOBAL – AJAX SAFE)
$(document).on('click', '.delete-task-image', function (e) {
    e.preventDefault();
    e.stopPropagation();

    const btn = $(this);
    const imageId = btn.data('id');

    console.log('DELETE IMAGE CLICKED:', imageId);

    if (!imageId) return;

    if (!confirm('Delete this image?')) return;

    $.ajax({
        url: '/task/delete-image',
        type: 'POST',
        dataType: 'json',
        data: {
            id: imageId,
            _csrf: yii.getCsrfToken()
        },
        success: function (res) {
            console.log('DELETE RESPONSE:', res);

            if (res.success) {
                btn.closest('.position-relative').remove();
            } else {
                alert(res.message || 'Delete failed');
            }
        },
        error: function () {
            alert('AJAX error while deleting image');
        }
    });
});
