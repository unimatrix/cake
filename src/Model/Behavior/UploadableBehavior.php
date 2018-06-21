<?php

namespace Unimatrix\Cake\Model\Behavior;

use Cake\Database\Type;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\Utility\Text;
use Unimatrix\Cake\Database\Type\FileType;
use Unimatrix\Cake\Validation\UploadValidation;
use RuntimeException;
use ArrayObject;

/**
 * Uploadable
 * Easy file upload for each defined field
 *
 * Configuration:
 * -------------------------------------------------------
 * Add this behavior in the model table:
 * $this->addBehavior('Unimatrix/Cake.Uploadable', [
 *     'fields' => [
 *         'file' => 'img/:model/:uuid'
 *     ]
 * ]);
 *
 * Field identifiers:
 * -------------------------------------------------------
 * :model: The model name
 * :uuid: A random and unique identifier UUID type
 * :md5: A random and unique identifier with 32 characters.
 *
 * Validation:
 * ---------------------------------------------------------------------------------
 * $validator
 *     ->requirePresence('file', 'create')
 *     ->allowEmpty('file', 'update');
 *
 * @author Flavius
 * @version 1.2
 */
class UploadableBehavior extends Behavior
{
    /**
     * Default config.
     * @var array
     */
    protected $_defaultConfig = [
        'root' => WWW_ROOT,
        'fields' => []
    ];

    /**
     * Check if configuration is set
     * @throws RuntimeException
     */
    private function check() {
        if(empty($this->_config['fields']))
            throw new RuntimeException('Configuration missing for UploadableBehavior');
    }

    /**
     * Build the behaviour
     * @param array $config Passed configuration
     * @return void
     */
    public function initialize(array $config) {
        // configuration set?
        $this->check();

        // load the file type & schema
        Type::map('unimatrix.file', FileType::class);
        $schema = $this->_table->getSchema();

        // go through each field and change the column type to our file type
        foreach($this->_config['fields'] as $field => $path)
            $schema->setColumnType($field, 'unimatrix.file');

        // update schema
        $this->_table->setSchema($schema);
    }

    /**
     * If a field is allowed to be empty as defined in the validation it should be unset to prevent processing
     * @param \Cake\Event\Event $event Event instance
     * @param ArrayObject $data Data to process
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options) {
        // configuration set?
        $this->check();

        // load validator and add our custom upload validator
        $validator = $this->_table->getValidator();
        $validator->setProvider('upload', UploadValidation::class);

        // go through each field
        foreach($this->_config['fields'] as $field => $path) {
            // add validators
            $validator->add($field, [
                'isUnderPhpSizeLimit' => ['rule' => 'isUnderPhpSizeLimit', 'provider' => 'upload', 'message' => __d('Unimatrix/cake', 'This file is too large')],
                'isUnderFormSizeLimit' => ['rule' => 'isUnderFormSizeLimit', 'provider' => 'upload', 'message' => __d('Unimatrix/cake', 'This file is too large')],
                'isCompletedUpload' => ['rule' => 'isCompletedUpload', 'provider' => 'upload', 'message' => __d('Unimatrix/cake', 'This file was only partially uploaded')],
                'isTemporaryDirectory' => ['rule' => 'isTemporaryDirectory', 'provider' => 'upload', 'message' => __d('Unimatrix/cake', 'Missing a temporary folder')],
                'isSuccessfulWrite' => ['rule' => 'isSuccessfulWrite', 'provider' => 'upload', 'message' => __d('Unimatrix/cake', 'Failed to write file to disk')],
                'isNotStoppedByExtension' => ['rule' => 'isNotStoppedByExtension', 'provider' => 'upload', 'message' => __d('Unimatrix/cake', 'Upload was stopped by extension')],
            ]);

            // empty allowed? && no file uploaded? unset field
            if($validator->isEmptyAllowed($field, false)
                && isset($data[$field]['error']) && $data[$field]['error'] === UPLOAD_ERR_NO_FILE)
                    unset($data[$field]);
        }
    }

    /**
     * beforeSave handle
     * @param \Cake\Event\Event  $event The beforeSave event that was fired.
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved.
     * @return void
     */
    public function beforeSave(Event $event, Entity $entity) {
        // configuration set?
        $this->check();

        // go through each field
        foreach($this->_config['fields'] as $field => $path) {
            // get uploaded file info
            $file = $entity->get($field);

            // no file or not array? problem...
            if(!($file && is_array($entity[$field])))
                continue;

            // file not ok? skip
            if($file['error'] !== UPLOAD_ERR_OK) {
                $entity->set($field, '');
                continue;
            }

            // get the final name
            $final = $this->formatFile($entity, $file, $path);

            // create the folder
            $folder = new Folder($this->_config['root']);
            $folder->create($this->_config['root'] . dirname($final));

            // copy file, delete old file and set new file in entity
            $file = new File($file['tmp_name']);
            if($file->copy($this->_config['root'] . $final)) {
                $this->deleteFile($entity->extractOriginalChanged([$field])[$field] ?? null);
                $entity->set($field, '/' . str_replace(DS, '/', $final));
            } else $entity->set($field, '');
        }
    }

    /**
     * afterDelete handle
     * @param \Cake\Event\Event  $event The beforeSave event that was fired.
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved.
     * @return void
     */
    public function afterDelete(Event $event, Entity $entity) {
        // configuration set?
        $this->check();

        // delete existing files linked to this entity
        foreach($this->_config['fields'] as $field => $path)
            $this->deleteFile($entity->get($field));
    }

    /**
     * Delete a file
     * @param string $file
     * @return boolean
     */
    private function deleteFile($file = null) {
        // no file
        if(!$file || is_array($file))
            return false;

        // get a file and delete it
        $file = new File($this->_config['root'] . trim(str_replace('/', DS, $file), DS));
        if($file->exists())
            return $file->delete();

        // nothing found
        return false;
    }

    /**
     * Get the path formatted without its identifiers to upload the file.
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved.
     * @param array $file The file array
     * @param string $path The path
     * @return string
     */
    private function formatFile(Entity $entity, $file = [], $path = false) {
        // get extension & path
        $ext = (new File($file['name']))->ext();
        $path = trim(str_replace(['/', '\\'], DS, $path), DS);

        // handle identifiers
        $identifiers = [
            ':model' => strtolower($entity->getSource()),
            ':uuid' => Text::uuid(),
            ':md5' => md5(rand() . uniqid() . time()),
        ];

        // output
        return strtr($path, $identifiers) . '.' . strtolower($ext);
    }
}
