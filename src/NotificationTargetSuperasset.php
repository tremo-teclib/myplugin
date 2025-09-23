<?php

namespace GlpiPlugin\Myplugin;

use NotificationTarget;

class NotificationTargetSuperasset extends NotificationTarget
{
    function __construct(
        $entity = '',
        $event = '',
        $object = null,
        $options = []
    ){
        parent::__construct($entity, $event, $object, $options);

        //adding the superasset name tag
        $this->addTagToList([
            'tag'   => 'superasset.name',
            'value' => true,
            'label' => __('Name of the superasset'),
            'lang'  => false,
        ]);
        //adding the superasset name tag

        //adding the computer's name tag
        $this->addTagToList([
            'tag'   => 'computer.name',
            'value' => true,
            'label' => __('Name of the computer'),
            'lang'  => false,
            'events' => ['computer_added']
        ]);
        //adding the computer's name tag
    }

    function getEvents(){
        return [
            'computer_added' => __('computer added', 'myplugin')
        ];
    }

    function addDataForTemplate($event, $options = [])
    {
        $this->data["##superasset.name##"] = $this->obj->fields['name'];

        if($event == 'computer_added'){
            $this->data["##computer.name##"] = __('Computer\'s name');
        }
    }

}