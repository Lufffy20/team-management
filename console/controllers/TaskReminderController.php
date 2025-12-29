<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\Task;
use yii\db\Expression;

class TaskReminderController extends Controller
{
    /**
     * 🔔 Due date reminder
     * - Today
     * - Tomorrow
     * - Avoid duplicate same-day reminders
     */
    public function actionDue()
    {
        $today    = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $tasks = Task::find()
            ->andWhere(['between', 'DATE(due_date)', $today, $tomorrow])
            ->andWhere(['!=', 'status', Task::STATUS_DONE])
            ->andWhere(['not', ['assignee_id' => null]])
            ->all();

        if (empty($tasks)) {
            echo "No due tasks found\n";
            return;
        }

        foreach ($tasks as $task) {

            $assignee = $task->assignee;

            // ❌ No assignee or email
            if (!$assignee || empty($assignee->email)) {
                continue;
            }

            // ❌ Skip duplicate reminder on same day
            if (
                $task->last_reminder_at &&
                date('Y-m-d', $task->last_reminder_at) === $today
            ) {
                continue;
            }

            /* ===============================
               📧 SEND REMINDER EMAIL
            =============================== */
            Yii::$app->mailer->compose(
                'taskDueReminder',
                [
                    'task'     => $task,
                    'assignee' => $assignee,
                ]
            )
            ->setTo($assignee->email)
            ->setFrom([Yii::$app->params['adminEmail'] => 'Task Manager'])
            ->setSubject('⏰ Task Due Reminder: ' . $task->title)
            ->send();

            /* ===============================
               🔒 MARK REMINDER SENT
            =============================== */
            $task->last_reminder_at = time();
            $task->save(false);

            echo "Reminder sent for Task #{$task->id}\n";
        }
    }

    /**
     * 🚨 Overdue task reminders
     */
    public function actionOverdue()
{
    $today = date('Y-m-d');

    $tasks = Task::find()
        ->andWhere(['<', 'due_date', $today])   // ✅ SIMPLE & CORRECT
        ->andWhere(['!=', 'status', Task::STATUS_DONE])
        ->andWhere(['not', ['assignee_id' => null]])
        ->all();

    if (empty($tasks)) {
        echo "No overdue tasks found\n";
        return;
    }

    foreach ($tasks as $task) {

        $assignee = $task->assignee;

        if (!$assignee || empty($assignee->email)) {
            continue;
        }

        //  Avoid spam (1 mail per day)
        if (
            $task->last_reminder_at &&
            date('Y-m-d', $task->last_reminder_at) === $today
        ) {
            continue;
        }

        Yii::$app->mailer->compose(
            'taskOverdueReminder',
            [
                'task'     => $task,
                'assignee' => $assignee,
            ]
        )
        ->setTo($assignee->email)
        ->setFrom([Yii::$app->params['adminEmail'] => 'Task Manager'])
        ->setSubject('🚨 Overdue Task: ' . $task->title)
        ->send();

        $task->last_reminder_at = time();
        $task->save(false);

        echo "Overdue reminder sent for Task #{$task->id}\n";
    }
}

}
