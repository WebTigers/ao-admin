<?php

class AOFormProfile extends Zend_Form {

    public function init ( ) {
        parent::init();
        $this->_addFormElements();
    }

    protected function _addFormElements()
    {

        $this->setName('AOFormProfile');

        $this->addElement($this->_getProfileImage());
        $this->addElement($this->_getFirstName());
        $this->addElement($this->_getLastName());
        $this->addElement($this->_getStageName());
        $this->addElement($this->_getEmail());
        $this->addElement($this->_getPhone());
        $this->addElement($this->_getProfileDescription());
        $this->addElement($this->_getChildProfile());

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

    protected function _getProfileImage()
    {
        $name = 'profile_image';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => [
                ['StringTrim'],
                ['PregReplace', [
                    'match' => '/[^A-Za-z0-9_\+\'\-\/,.\:\#\&\=\%]/',
                    'replace' => ''
                ]]
            ],
            'validators' => [
                ['StringLength', false, [
                    'min' => 0,
                    'max' => 100,
                    'messages' => [
                        Zend_Validate_StringLength::TOO_SHORT => "Entry is too short.",
                        Zend_Validate_StringLength::TOO_LONG => "Entry is too long.",
                    ]
                ]],
                ['Regex', false, [
                    'pattern' => '/^[A-Za-z0-9_\+\'\-\/,.\:\#\&\=\%]+$/',
                    'messages' => [Zend_Validate_Regex::NOT_MATCH => "Invalid characters."]
                ]]
            ],
        ];

        return new Zend_Form_Element_Hidden($name, $options);

    }

    protected function _getFirstName()
    {
        $name = 'first_name';

        $options = [
            'name' => $name,
            'required' => true,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getLastName()
    {

        $name = 'last_name';

        $options = [
            'name' => $name,
            'required' => true,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getStageName()
    {

        $name = 'stage_name';

        $options = [
            'name' => $name,
            'required' => true,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getEmail()
    {

        $name = 'email';

        $options = [

            'name' => $name,

            'required' => true,

            'filters' => [
                ['StringTrim'],
            ],

        ];

        $validators = $this->getStringValidators();
        unset( $validators[2] ); // Unsets the Regex validator
        array_push( $validators, ['EmailAddress', false, [
            'messages' => [
                Zend_Validate_EmailAddress::INVALID_FORMAT => "Invalid format.",
                Zend_Validate_EmailAddress::INVALID_HOSTNAME => "Invalid hostname.",
                Zend_Validate_EmailAddress::DOT_ATOM => "Invalid user.",
            ],
        ]]);

        $options['validators'] = $validators;

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getPhone()
    {

        $name = 'phone';

        $options = [
            'name' => $name,
            'required' => true,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getProfileDescription()
    {

        $name = 'profile_description';

        $options = [
            'name' => $name,
            'required' => true,
            'filters' => [
                ['StringTrim'],
                ['PregReplace', [
                    'match' => '/[^A-Za-z0-9 \'\-\/,.]/',
                    'replace' => ''
                ]]
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
                ['Regex', false, [
                    'pattern' => '/^[A-Za-z0-9 \'\-\/,.]+$/',
                    'messages' => [Zend_Validate_Regex::NOT_MATCH => "Invalid characters."]
                ]]
            ],
        ];

        return new Zend_Form_Element_Textarea($name, $options);

    }

    protected function _getChildProfile()
    {

        $name = 'child_profile';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }


}