<?php

namespace Unimatrix\Cake\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\Utility\Text;

/**
 * Sluggable
 * Slugify a field from 'A nice article' to 'a-nice-article'
 *
 * field: the db field to take the slug string from (title, name)
 * Replacement: replace invalid characters with this character
 * Overwrite: overwrite the slug upon edidting the entity?
 * Unique: check if the slug already exists and make it unique
 *
 * Configuration:
 * -------------------------------------------------------
 * $this->addBehavior('Unimatrix/Cake.Sluggable', [
 *     'field' => 'title',
 *     'replacement' => '-',
 *     'overwrite' => false,
 *     'unique' => true
 * ]);
 *
 * Note:
 * The slug field should be called 'slug' in the database table
 *
 * Slug finder:
 * -----------------------------------
 * $this->Users->find('slug', ['your-slug-here']);
 *
 * @author Flavius
 * @version 1.0
 */
class SluggableBehavior extends Behavior
{
    /**
     * Default config.
     * @var array
     */
    protected $_defaultConfig = [
        'field' => 'title',
        'replacement' => '-',
        'overwrite' => false,
        'unique' => true
    ];

    /**
     * Slug a field passed in the default config with its replacement.
     * @param $value The string that needs to be processed
     * @return string
     */
    private function slug($value = null) {
        // generate slug
        $slug = strtolower(Text::slug($value, $this->_config['replacement']));

        // unique slug?
        if($this->_config['unique']) {
            // does the slug already exist?
            $field = $this->_table->getAlias() . '.slug';
            $conditions = [$field => $slug];
            $suffix = '';
            $i = 0;

            // loop till unique slug is found
            while ($this->_table->exists($conditions)) {
                $i++;
                $suffix    = $this->_config['replacement'] . $i;
                $conditions[$field] = $slug . $suffix;
            }

            // got suffix? append it
            if($suffix)
                $slug .= $suffix;
        }

        // return slug
        return $slug;
    }

    /**
     * BeforeSave handle.
     * @param \Cake\Event\Event  $event The beforeSave event that was fired.
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved.
     * @return void
     */
    public function beforeSave(Event $event, Entity $entity) {
        if(!$entity->get('slug') || $this->_config['overwrite'])
            $entity->set('slug', $this->slug($entity->get($this->_config['field'])));
    }

    /**
     * Custom finder by slug.
     * @param \Cake\ORM\Query $query The query finder.
     * @param array $options The options passed in the query builder.
     * @return \Cake\ORM\Query
     */
    public function findSlug(Query $query, array $options) {
        return $query->where([
            'slug' => $options[0]
        ]);
    }
}
