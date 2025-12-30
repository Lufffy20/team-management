<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\Task;
use common\models\Notification;

/**
 * TaskReminderController
 *
 * Console controller used for sending:
 * - Due date reminders
 * - Overdue task reminders
 *
 * These actions are usually triggered via cron jobs.
 */
class TaskReminderController extends Controller
{
    /**
     * DUE DATE REMINDER
     * --------------------------------------------------
     * Sends reminders for tasks that are:
     * - Due today OR tomorrow
     * - Not completed
     * - Assigned to a user
     *
     * Safeguards:
     * - Avoids duplicate reminders on the same day
     */
    public function actionDue()
    {
        // Today and tomorrow dates
        $today    = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        /* ===============================
         * FETCH DUE TASKS
         * =============================== */
        $tasks = Task::find()
            ->andWhere(['between', 'DATE(due_date)', $today, $tomorrow])
            ->andWhere(['!=', 'status', Task::STATUS_DONE])
            ->andWhere(['not', ['assignee_id' => null]])
            ->all();

        // No tasks found
        if (empty($tasks)) {
            echo "No due tasks found\n";
            return;
        }

        foreach ($tasks as $task) {

            // Assigned user
            $assignee = $task->assignee;

            // Skip if no assignee or email missing
            if (!$assignee || empty($assignee->email)) {
                continue;
            }

            /**
             * ❌ Prevent duplicate reminder
             * One reminder per task per day
             */
            if (
                $task->last_reminder_at &&
                date('Y-m-d', $task->last_reminder_at) === $today
            ) {
                continue;
            }

            /* ===============================
             * SEND REMINDER EMAIL
             * =============================== */
            Yii::$app->mailer->compose(
                'taskDueReminder',
                [
                    'task'     => $task,
                    'assignee' => $assignee,
                ]
            )
            ->setTo($assignee->email)
            ->setFrom([Yii::$app->params['adminEmail'] => 'Task Manager'])
            ->setSubject('Task Due Reminder: ' . $task->title)
            ->send();

            /* ===============================
             * CREATE IN-APP NOTIFICATION
             * =============================== */
            $notification = new Notification();
            $notification->user_id = $assignee->id;
            $notification->title   = 'Task Due Reminder';
            $notification->message =
                'Task "' . $task->title . '" is due soon.';
            $notification->save(false);

            /* ===============================
             * UPDATE LAST REMINDER TIMESTAMP
             * =============================== */
            $task->last_reminder_at = time();
            $task->save(false);

            echo "Reminder sent for Task #{$task->id}\n";
        }
    }

    /**
     * OVERDUE TASK REMINDER
     * --------------------------------------------------
     * Sends reminders for tasks that are:
     * - Past due date
     * - Not completed
     * - Assigned to a user
     *
     * Safeguards:
     * - Only one reminder per task per day
     */
    public function actionOverdue()
    {
        $today = date('Y-m-d');

        /* ===============================
         * FETCH OVERDUE TASKS
         * =============================== */
        $tasks = Task::find()
            ->andWhere(['<', 'due_date', $today])
            ->andWhere(['!=', 'status', Task::STATUS_DONE])
            ->andWhere(['not', ['assignee_id' => null]])
            ->all();

        // No overdue tasks
        if (empty($tasks)) {
            echo "No overdue tasks found\n";
            return;
        }

        foreach ($tasks as $task) {

            // Assigned user
            $assignee = $task->assignee;

            // Skip if no assignee or email missing
            if (!$assignee || empty($assignee->email)) {
                continue;
            }

            /**
             * ❌ Prevent notification spam
             * Only one reminder per day
             */
            if (
                $task->last_reminder_at &&
                date('Y-m-d', $task->last_reminder_at) === $today
            ) {
                continue;
            }

            /* ===============================
             * SEND OVERDUE EMAIL
             * =============================== */
            Yii::$app->mailer->compose(
                'taskOverdueReminder',
                [
                    'task'     => $task,
                    'assignee' => $assignee,
                ]
            )
            ->setTo($assignee->email)
            ->setFrom([Yii::$app->params['adminEmail'] => 'Task Manager'])
            ->setSubject('Overdue Task: ' . $task->title)
            ->send();

            /* ===============================
             * CREATE IN-APP NOTIFICATION
             * =============================== */
            $notification = new Notification();
            $notification->user_id = $assignee->id;
            $notification->title   = 'Overdue Task';
            $notification->message =
                'Task "' . $task->title . '" is overdue.';
            $notification->save(false);

            /* ===============================
             * UPDATE LAST REMINDER TIMESTAMP
             * =============================== */
            $task->last_reminder_at = time();
            $task->save(false);

            echo "Overdue reminder sent for Task #{$task->id}\n";
        }
    }
}
