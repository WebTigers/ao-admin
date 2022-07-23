<?php

class AOFormMedia extends Zend_Form {

    public function init ( ) {
        parent::init();
        $this->_addFormElements();
    }

    protected function _addFormElements()
    {

        $this->setName('AOFormMedia');

        $this->addElement($this->_getMediaId());
        $this->addElement($this->_getMediaAltText());
        $this->addElement($this->_getMediaTitle());
        $this->addElement($this->_getMediaCaption());
        $this->addElement($this->_getMediaDescription());
        $this->addElement($this->_getMediaCategories());

    }

    protected function getStringFilters () {

        return [
            ['StringTrim'],
            ['PregReplace', [
                'match' => '/[^A-Za-z0-9 \'\"\-\/,.\:\#\&]/',
                'replace' => ''
            ]]
        ];

    }

    protected function getStringValidators () {

        return [
            ['NotEmpty', false, [
                'messages' => [Zend_Validate_NotEmpty::IS_EMPTY => "Required"]
            ]],
            ['StringLength', false, [
                'min' => 1,
                'max' => 100,
                'messages' => [
                    Zend_Validate_StringLength::TOO_SHORT => "Entry is too short.",
                    Zend_Validate_StringLength::TOO_LONG => "Entry is too long.",
                ]
            ]],
            ['Regex', false, [
                'pattern' => '/^[A-Za-z0-9 \'\"\-\/,.\:\#\&]+$/',
                'messages' => [Zend_Validate_Regex::NOT_MATCH => "Invalid characters."]
            ]]
        ];

    }

    // Form Fields //

    protected function _getMediaId()
    {
        $name = 'media_id';

        $options = [
            'name' => $name,
            'required' => true,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getMediaAltText()
    {
        $name = 'media_alt_text';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getMediaTitle()
    {

        $name = 'media_title';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getMediaCaption()
    {

        $name = 'media_caption';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => [
                ['StringTrim'],
//                ['PregReplace', [
//                    'match' => '/[^A-Za-z0-9 \'\-\/,.]/',
//                    'replace' => ''
//                ]]
            ],
            'validators' => [
                ['NotEmpty', false, [
                    'messages' => [Zend_Validate_NotEmpty::IS_EMPTY => "Required"]
                ]],
                ['StringLength', false, [
                    'min' => 1,
                    'max' => 5000,
                    'messages' => [
                        Zend_Validate_StringLength::TOO_SHORT => "Entry is too short.",
                        Zend_Validate_StringLength::TOO_LONG => "Entry is too long.",
                    ]
                ]],
//                ['Regex', false, [
//                    'pattern' => '/^[A-Za-z0-9 \'\-\/,.]+$/',
//                    'messages' => [Zend_Validate_Regex::NOT_MATCH => "Invalid characters."]
//                ]]
            ],
        ];

        return new Zend_Form_Element_Textarea($name, $options);

    }

    protected function _getMediaDescription()
    {

        $name = 'media_description';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => [
                ['StringTrim'],
//                ['PregReplace', [
//                    'match' => '/[^A-Za-z0-9 \'\-\/,.]/',
//                    'replace' => ''
//                ]]
            ],
            'validators' => [
                ['NotEmpty', false, [
                    'messages' => [Zend_Validate_NotEmpty::IS_EMPTY => "Required"]
                ]],
                ['StringLength', false, [
                    'min' => 1,
                    'max' => 5000,
                    'messages' => [
                        Zend_Validate_StringLength::TOO_SHORT => "Entry is too short.",
                        Zend_Validate_StringLength::TOO_LONG => "Entry is too long.",
                    ]
                ]],
//                ['Regex', false, [
//                    'pattern' => '/^[A-Za-z0-9 \'\-\/,.]+$/',
//                    'messages' => [Zend_Validate_Regex::NOT_MATCH => "Invalid characters."]
//                ]]
            ],
        ];

        return new Zend_Form_Element_Textarea($name, $options);

    }

    protected function _getMediaCategories()
    {
        $name = 'media_categories';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }


}