<?php

class AOFormResume extends Zend_Form {

    public function init ( ) {
        parent::init();
        $this->_addFormElements();
    }

    protected function _addFormElements()
    {

        $this->setName('AOFormResume');

        $this->addElement($this->_getResume());

    }

    // Form Fields //

    protected function _getResume()
    {

        $name = 'resume';

        $options = [
            'name' => $name,
            'required' => true,
            'filters' => [
                ['StringTrim'],
//                ['PregReplace', [
//                    'match' => '/[^A-Za-z0-9 \'\-\/,.<>"&]/',
//                    'replace' => ''
//                ]]
            ],
            'validators' => [
                ['NotEmpty', false, [
                    'messages' => [Zend_Validate_NotEmpty::IS_EMPTY => "Required"]
                ]],
                ['StringLength', false, [
                    'min' => 0,
                    'max' => 100000,
                    'messages' => [
                        Zend_Validate_StringLength::TOO_SHORT => "Entry is too short.",
                        Zend_Validate_StringLength::TOO_LONG => "Entry is too long.",
                    ]
                ]],
//                ['Regex', false, [
//                    'pattern' => '/^[A-Za-z0-9 \'\-\/,.<>"&]+$/',
//                    'messages' => [Zend_Validate_Regex::NOT_MATCH => "Invalid characters."]
//                ]]
            ],
        ];

        return new Zend_Form_Element_Textarea($name, $options);

    }

}