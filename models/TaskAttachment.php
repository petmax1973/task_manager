<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "task_attachment".
 *
 * @property int $id
 * @property int $task_id
 * @property string $original_name
 * @property string $stored_name
 * @property string|null $mime_type
 * @property int $file_size
 * @property int $created_at
 *
 * @property Task $task
 */
class TaskAttachment extends ActiveRecord
{
    public static function tableName()
    {
        return 'task_attachment';
    }

    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['task_id', 'original_name', 'stored_name', 'file_size'], 'required'],
            [['task_id', 'file_size'], 'integer'],
            [['original_name', 'stored_name'], 'string', 'max' => 255],
            [['mime_type'], 'string', 'max' => 100],
            [['task_id'], 'exist', 'targetClass' => Task::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'Task ID',
            'original_name' => Yii::t('app', 'File Name'),
            'file_size' => Yii::t('app', 'Size'),
            'created_at' => Yii::t('app', 'Uploaded At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    /**
     * Generate a unique stored name for the file.
     * @param string $extension
     * @return string
     */
    public static function generateStoredName($extension)
    {
        return uniqid('att_', true) . '_' . time() . '.' . $extension;
    }

    /**
     * Get human-readable file size.
     * @return string
     */
    public function getFormattedSize()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Get the full path to the stored file.
     * @return string
     */
    public function getFilePath()
    {
        return Yii::getAlias('@webroot/uploads/task-attachments/') . $this->stored_name;
    }

    /**
     * Check if the attachment is a video file.
     * @return bool
     */
    public function isVideo()
    {
        return strpos($this->mime_type, 'video/') === 0;
    }

    /**
     * Check if the attachment is an image file.
     * @return bool
     */
    public function isImage()
    {
        return strpos($this->mime_type, 'image/') === 0;
    }

    /**
     * Get the Font Awesome icon class based on file type.
     * @return string
     */
    public function getFileIcon()
    {
        if ($this->isImage()) {
            return 'fa-file-image';
        }
        if ($this->isVideo()) {
            return 'fa-file-video';
        }
        if (strpos($this->mime_type, 'audio/') === 0) {
            return 'fa-file-audio';
        }
        if (strpos($this->mime_type, 'application/pdf') === 0) {
            return 'fa-file-pdf';
        }
        $ext = pathinfo($this->original_name, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['doc', 'docx', 'odt'])) {
            return 'fa-file-word';
        }
        if (in_array(strtolower($ext), ['xls', 'xlsx', 'ods'])) {
            return 'fa-file-excel';
        }
        if (in_array(strtolower($ext), ['zip', 'rar', '7z', 'tar', 'gz'])) {
            return 'fa-file-archive';
        }
        return 'fa-file';
    }
}
