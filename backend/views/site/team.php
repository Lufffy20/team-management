<?php
use yii\helpers\Url;
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Team Members</h4>

        <a href="<?= Url::to(['team/create']) ?>" class="btn btn-primary btn-sm" disabled>
            <i class="bx bx-user-plus"></i> Add Member (Coming Soon)
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <!-- DUMMY STATIC ROWS (Until real DB setup) -->
                    <tr>
                        <td>
                            <img src="https://ui-avatars.com/api/?name=John+Doe"
                                class="rounded-circle" width="40">
                        </td>
                        <td>John Doe</td>
                        <td>john@example.com</td>
                        <td>
                            <span class="badge bg-label-primary">Admin</span>
                        </td>
                        <td>
                            <span class="badge bg-label-success">Active</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                <i class="bx bx-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" disabled>
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <img src="https://ui-avatars.com/api/?name=Meet+Parmar"
                                class="rounded-circle" width="40">
                        </td>
                        <td>Meet Parmar</td>
                        <td>meet@example.com</td>
                        <td>
                            <span class="badge bg-label-info">Team Member</span>
                        </td>
                        <td>
                            <span class="badge bg-label-success">Active</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                <i class="bx bx-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" disabled>
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <img src="https://ui-avatars.com/api/?name=Rahul+Shah"
                                class="rounded-circle" width="40">
                        </td>
                        <td>Rahul Shah</td>
                        <td>rahul@example.com</td>
                        <td>
                            <span class="badge bg-label-warning">Viewer</span>
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">Pending</span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                <i class="bx bx-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" disabled>
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>
