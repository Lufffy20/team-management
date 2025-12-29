<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\web\UploadedFile;

/**
 * Task model
 *
 * This model represents the `task` table.
 * It handles tasks, attachments, images, subtasks, comments, and relations.
 */
class Task extends ActiveRecord
{
    /* ================= PUBLIC PROPERTIES ================= */

    public $imageFiles;
    public $attachmentFiles;

    /* ================= STATUS CONSTANTS ================= */

    const STATUS_TODO        = 'todo';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE        = 'done';
    const STATUS_ARCHIVED    = 'archived';

    /* ================= PRIORITY CONSTANTS ================= */

    const PRIORITY_LOW    = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH   = 'high';

    /**
     * Returns the database table name.
     */
    public static function tableName()
    {
        return '{{%task}}';
    }

    /**
     * Attaches behaviors to the model.
     * - TimestampBehavior for created_at & updated_at
     * - BlameableBehavior for created_by & updated_by
     */
    public function behaviors()
    {
        return [
            'timestamp' => TimestampBehavior::class,

            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * Validation rules for Task model.
     */
    public function rules()
    {
        return [
            // Required fields
            [['title', 'board_id'], 'required'],

            // Description text
            [['description'], 'string'],

            // Image upload validation
            [['imageFiles'], 'file',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'maxFiles' => 10,
                'skipOnEmpty' => true,
                'checkExtensionByMimeType' => false,
            ],

            // Attachment upload validation
            ['attachmentFiles', 'file',
                'maxFiles' => 5,
                'extensions' => ['jpg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'],
                'skipOnEmpty' => true,
            ],

            // Custom validation for total attachment size
            ['attachmentFiles', 'validateTotalAttachmentSize'],

            // Integer fields
            [['last_reminder_at'], 'integer'],
            [['assignee_id', 'sort_order', 'created_by', 'board_id'], 'integer'],

            // Date format
            [['due_date'], 'date', 'format' => 'php:Y-m-d'],

            // Title length
            [['title'], 'string', 'max' => 255],

            // Status validation
            [['status'], 'in', 'range' => [
                self::STATUS_TODO,
                self::STATUS_IN_PROGRESS,
                self::STATUS_DONE,
                self::STATUS_ARCHIVED,
            ]],

            // Priority validation
            [['priority'], 'in', 'range' => [
                self::PRIORITY_LOW,
                self::PRIORITY_MEDIUM,
                self::PRIORITY_HIGH,
            ]],
        ];
    }

    /**
     * Validates total size of all attachments.
     * Maximum allowed size is 10 MB.
     */
    public function validateTotalAttachmentSize($attribute, $params)
    {
        if (empty($this->$attribute)) {
            return;
        }

        $totalSize = 0;

        foreach ($this->$attribute as $file) {
            $totalSize += $file->size;
        }

        // 10 MB total size limit
        if ($totalSize > (10 * 1024 * 1024)) {
            $this->addError(
                $attribute,
                'Total attachment size must not exceed 10 MB.'
            );
        }
    }

    /* ================= RELATIONS ================= */

    /**
     * Task assignee relation.
     */
    public function getAssignee()
    {
        return $this->hasOne(User::class, ['id' => 'assignee_id']);
    }

    /**
     * Assigned user relation (alias).
     */
    public function getAssignedUser()
    {
        return $this->hasOne(User::class, ['id' => 'assignee_id']);
    }

    /**
     * Board relation.
     */
    public function getBoard()
    {
        return $this->hasOne(Board::class, ['id' => 'board_id']);
    }

    /**
     * Team relation (via board).
     */
    public function getTeam()
    {
        return $this->hasOne(Team::class, ['id' => 'team_id']);
    }

    /**
     * Subtasks relation.
     */
    public function getSubtasks()
    {
        return $this->hasMany(Subtask::class, ['task_id' => 'id']);
    }

    /**
     * Creator relation.
     */
    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Tasks relation by board.
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['board_id' => 'id']);
    }

    /**
     * Task images relation.
     */
    public function getImages()
    {
        return $this->hasMany(TaskImage::class, ['task_id' => 'id']);
    }

    /**
     * Created by user relation.
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Updated by user relation.
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Task attachments relation.
     */
    public function getAttachments()
    {
        return $this->hasMany(TaskAttachment::class, ['task_id' => 'id']);
    }

    /**
     * Task comments relation.
     */
    public function getComments()
    {
        return $this->hasMany(TaskComment::class, ['task_id' => 'id'])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /* ================= IMAGE UPLOAD ================= */

    /**
     * Uploads task images and saves records.
     */
    public function uploadImages()
    {
        if (empty($this->imageFiles)) {
            return;
        }

        $path = Yii::getAlias('@webroot/uploads/tasks/');
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        foreach ($this->imageFiles as $file) {
            $name = 'task_' . $this->id . '_' . uniqid() . '.' . $file->extension;

            if ($file->saveAs($path . $name)) {
                $img = new TaskImage();
                $img->task_id = $this->id;
                $img->image   = $name;
                $img->save(false);
            }
        }
    }

    /* ================= HELPERS ================= */

    /**
     * Returns task status labels.
     */
    public static function statuses()
    {
        return [
            self::STATUS_TODO        => 'To-Do',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_DONE        => 'Done',
            self::STATUS_ARCHIVED    => 'Archived',
        ];
    }

    /**
     * Returns task priority labels.
     */
    public static function priorities()
    {
        return [
            self::PRIORITY_LOW    => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH   => 'High',
        ];
    }

    /**
     * Uploads task attachments and saves records.
     */
    public function uploadAttachments()
    {
        foreach ($this->attachmentFiles as $file) {
            $name = 'task_' . $this->id . '_' . uniqid() . '.' . $file->extension;
            $path = Yii::getAlias('@webroot/uploads/tasks/') . $name;

            if ($file->saveAs($path)) {
                $a = new TaskAttachment();
                $a->task_id = $this->id;
                $a->file = $name;
                $a->save(false);
            }
        }
    }
}
