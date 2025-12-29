<div class="row g-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm task-card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <span class="badge bg-primary-subtle border border-primary text-primary me-2">In Progress</span>
            <span class="badge bg-danger-subtle border border-danger text-danger task-badge-priority">High Priority</span>
            <h4 class="mt-2 mb-1">Implement task creation API</h4>
            <div class="text-muted small">Project: Backend API</div>
          </div>
          <button class="btn btn-sm btn-outline-secondary">Edit</button>
        </div>

        <hr>

        <div class="row small mb-3">
          <div class="col-md-4">
            <div class="text-muted">Assignee</div>
            <div class="d-flex align-items-center mt-1">
              <img src="https://via.placeholder.com/32" class="avatar-sm me-2" alt="">
              <span>John Doe</span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Due Date</div>
            <div class="mt-1">28 Nov 2025</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Created By</div>
            <div class="mt-1">Fejan · 26 Nov 2025</div>
          </div>
        </div>

        <div class="mb-3 small">
          <div class="text-muted mb-1">Description</div>
          <p class="mb-0">
            Create RESTful API endpoint for task creation including validation, assignment, and email notification trigger.
          </p>
        </div>

        <div class="mb-3 small">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="text-muted">Subtasks</div>
            <button class="btn btn-sm btn-outline-primary btn-sm">+ Add Subtask</button>
          </div>
          <ul class="list-group list-group-flush">
            <li class="list-group-item px-0 d-flex align-items-center">
              <input class="form-check-input me-2" type="checkbox">
              <span>Define request & response schema</span>
            </li>
            <li class="list-group-item px-0 d-flex align-items-center">
              <input class="form-check-input me-2" type="checkbox" checked>
              <span class="text-muted text-decoration-line-through">Create Task model</span>
            </li>
          </ul>
        </div>

        <div class="small">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="text-muted">Attachments</div>
            <button class="btn btn-sm btn-outline-secondary">Upload</button>
          </div>
          <div class="border rounded p-2 bg-light">
            <div class="d-flex justify-content-between">
              <div>
                <i class="bi bi-file-earmark-text me-1"></i>
                api-spec.pdf
              </div>
              <a href="#" class="small">Download</a>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Comments -->
    <div class="card border-0 shadow-sm task-card">
      <div class="card-header bg-white border-0">
        <h6 class="mb-0">Comments</h6>
      </div>
      <div class="card-body small">
        <div class="mb-3 d-flex">
          <img src="https://via.placeholder.com/32" class="avatar-sm me-2" alt="">
          <div class="flex-grow-1">
            <textarea class="form-control form-control-sm mb-2" rows="2" placeholder="Add a comment..."></textarea>
            <div class="text-end">
              <button class="btn btn-sm btn-primary">Post Comment</button>
            </div>
          </div>
        </div>

        <div class="mb-3 d-flex">
          <img src="https://via.placeholder.com/32" class="avatar-sm me-2" alt="">
          <div>
            <div class="fw-semibold">Fejan <span class="text-muted small">· 10 min ago</span></div>
            <div>Make sure to add validation for assignee role.</div>
          </div>
        </div>

        <div class="mb-3 d-flex">
          <img src="https://via.placeholder.com/32" class="avatar-sm me-2" alt="">
          <div>
            <div class="fw-semibold">You <span class="text-muted small">· 2 min ago</span></div>
            <div>Sure, I will handle that in the request form.</div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Right side: Activity -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm task-card">
      <div class="card-header bg-white border-0">
        <h6 class="mb-0">Activity Timeline</h6>
      </div>
      <div class="card-body small">
        <ul class="list-unstyled mb-0">
          <li class="mb-3">
            <div class="fw-semibold">Status changed to In Progress</div>
            <div class="text-muted">You · 5 min ago</div>
          </li>
          <li class="mb-3">
            <div class="fw-semibold">Task assigned to John</div>
            <div class="text-muted">harsh · 1 hour ago</div>
          </li>
          <li class="mb-3">
            <div class="fw-semibold">Task created</div>
            <div class="text-muted">Fejan · 26 Nov 2025</div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
