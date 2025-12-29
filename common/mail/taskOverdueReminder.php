<?php
/** @var $task common\models\Task */
/** @var $assignee common\models\User */
?>

<div style="font-family: Arial, Helvetica, sans-serif; background-color:#f5f6f8; padding:30px;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:6px; overflow:hidden;">

        <!-- HEADER -->
        <div style="background:#d32f2f; color:#ffffff; padding:16px 20px;">
            <h2 style="margin:0; font-size:20px;">🚨 Overdue Task Reminder</h2>
        </div>

        <!-- BODY -->
        <div style="padding:20px; color:#333333; font-size:14px; line-height:1.6;">

            <p style="margin-top:0;">
                Hello <strong><?= htmlspecialchars($assignee->username ?? 'Team Member') ?></strong>,
            </p>

            <p>
                This is a reminder that the following task assigned to you is
                <strong style="color:#d32f2f;">overdue</strong>.
                Please review the details below and take the necessary action.
            </p>

            <!-- TASK DETAILS -->
            <table width="100%" cellpadding="0" cellspacing="0"
                   style="border-collapse:collapse; margin:20px 0;">
                <tr>
                    <td style="padding:8px 0; width:140px; color:#777;">Task Title</td>
                    <td style="padding:8px 0;"><strong><?= htmlspecialchars($task->title) ?></strong></td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#777;">Due Date</td>
                    <td style="padding:8px 0;"><?= date('d M Y', strtotime($task->due_date)) ?></td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#777;">Priority</td>
                    <td style="padding:8px 0;"><?= ucfirst($task->priority ?? 'Medium') ?></td>
                </tr>
                <?php if (!empty($task->description)) : ?>
                <tr>
                    <td style="padding:8px 0; color:#777;">Description</td>
                    <td style="padding:8px 0;">
                        <?= nl2br(htmlspecialchars($task->description)) ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>

            <!-- NOTE -->
            <p style="background:#fff3e0; padding:12px; border-left:4px solid #ff9800;">
                ⚠️ Please complete this task as soon as possible to avoid further delays.
            </p>

            <p>
                If you have already completed this task, please update its status
                in the system accordingly.
            </p>

            <p style="margin-bottom:0;">
                Thank you,<br>
                <strong>Task Management System</strong>
            </p>
        </div>

        <!-- FOOTER -->
        <div style="background:#f1f1f1; padding:12px 20px; font-size:12px; color:#777;">
            This is an automated reminder. Please do not reply to this email.
        </div>

    </div>
</div>
