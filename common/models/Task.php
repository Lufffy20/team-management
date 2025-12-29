<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\web\UploadedFile;

class Task extends ActiveRecord
{
    public $imageFiles;
    public $attachmentFiles;

    const STATUS_TODO        = 'todo';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE        = 'done';
    const STATUS_ARCHIVED    = 'archived';

    const PRIORITY_LOW    = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH   = 'high';

    public static function tableName()
    {
        return '{{%task}}';
    }

    
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
    public function rules()
    {
        return [
            [['title', 'board_id'], 'required'],
            [['description'], 'string'],

            [['imageFiles'], 'file',
                'extensions' => ['jpg','jpeg','png','webp'],
                'maxFiles' => 10,
                'skipOnEmpty' => true,
                'checkExtensionByMimeType' => false
            ],

            ['attachmentFiles', 'file',
    'maxFiles' => 5,
    'extensions' => ['jpg','png','pdf','doc','docx','xls','xlsx','zip','rar'],
    'skipOnEmpty' => true
],

['attachmentFiles', 'validateTotalAttachmentSize'],


            [['last_reminder_at'], 'integer'],

            [['due_date'], 'date', 'format' => 'php:Y-m-d'],
            [['assignee_id', 'sort_order', 'created_by', 'board_id'], 'integer'],
            [['title'], 'string', 'max' => 255],

            [['status'], 'in', 'range' => [
                self::STATUS_TODO,
                self::STATUS_IN_PROGRESS,
                self::STATUS_DONE,
                self::STATUS_ARCHIVED
            ]],

            [['priority'], 'in', 'range' => [
                self::PRIORITY_LOW,
                self::PRIORITY_MEDIUM,
                self::PRIORITY_HIGH
            ]],
        ];
    }


    public function validateTotalAttachmentSize($attribute, $params)
{
    if (empty($this->$attribute)) {
        return;
    }

    $totalSize = 0;

    foreach ($this->$attribute as $file) {
        $totalSize += $file->size;
    }

    // 10 MB TOTAL
    if ($totalSize > (10 * 1024 * 1024)) {
        $this->addError(
            $attribute,
            'Total attachment size must not exceed 10 MB.'
        );
    }
}


    /* ================= RELATIONS ================= */
    public function getAssignee()
{
    return $this->hasOne(User::class, ['id' => 'assignee_id']);
}


public function getAssignedUser()
{
    return $this->hasOne(\common\models\User::class, ['id' => 'assignee_id']);
}

public function getProject()
{
    return $this->hasOne(\common\models\Project::class, ['id' => 'project_id']);
}

public function getBoard()
{
    return $this->hasOne(Board::class, ['id' => 'board_id']);
}

// common/models/Board.php
public function getTeam()
{
    return $this->hasOne(Team::class, ['id' => 'team_id']);
}


public function getSubtasks(){
    return $this->hasMany(\common\models\Subtask::class, ['task_id'=>'id']);
}

public function getCreator()
{
    return $this->hasOne(\common\models\User::class, ['id' => 'created_by']);
}


public function getTasks()
{
    return $this->hasMany(Task::class, ['board_id' => 'id']);
}

    public function getImages()
    {
        return $this->hasMany(TaskImage::class, ['task_id' => 'id']);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    public function getAttachments()
{
    return $this->hasMany(TaskAttachment::class, ['task_id' => 'id']);
}

public function getComments()
{
    return $this->hasMany(TaskComment::class, ['task_id' => 'id'])
        ->orderBy(['created_at' => SORT_DESC]);
}

    

    /* ================= IMAGE UPLOAD ================= */

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

    public static function statuses()
    {
        return [
            self::STATUS_TODO        => 'To-Do',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_DONE        => 'Done',
            self::STATUS_ARCHIVED    => 'Archived',
        ];
    }

    public static function priorities()
    {
        return [
            self::PRIORITY_LOW    => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH   => 'High',
        ];
    }


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
