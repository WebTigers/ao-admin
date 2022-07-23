<?php

class AOFormSizeCard extends Zend_Form {

    public function init ( ) {
        parent::init();
        $this->_addFormElements();
    }

    protected function _addFormElements()
    {

        $this->setName('AOFormSizeCard');

        $this->addElement($this->_getHeight());
        $this->addElement($this->_getWeight());
        $this->addElement($this->_getEyes());
        $this->addElement($this->_getHair());
        $this->addElement($this->_getHairLength());
        $this->addElement($this->_getBodyType());

        $this->addElement($this->_getShirt());
        $this->addElement($this->_getSleeve());
        $this->addElement($this->_getNeck());
        $this->addElement($this->_getJacket());

        $this->addElement($this->_getWaist());
        $this->addElement($this->_getInseam());

        $this->addElement($this->_getShoes());
        $this->addElement($this->_getGloves());
        $this->addElement($this->_getHat());
        $this->addElement($this->_getWardrobeNotes());

    }

    protected function getStringFilters () {

        return [
            ['StringTrim'],
            ['PregReplace', [
                'match' => '/[^A-Za-z0-9 \'\-\/\"\\,.]/',
                'replace' => ''
            ]]
        ];

    }

    protected function getStringValidators () {

        return [
            // ['NotEmpty', false, [
            //    'messages' => [Zend_Validate_NotEmpty::IS_EMPTY => "Required"]
            // ]],
            ['StringLength', false, [
                'min' => 1,
                'max' => 50,
                'messages' => [
                    Zend_Validate_StringLength::TOO_SHORT => "Entry is too short.",
                    Zend_Validate_StringLength::TOO_LONG => "Entry is too long.",
                ]
            ]],
            ['Regex', false, [
                'pattern' => '/^[A-Za-z0-9 \'\-\/\"\\,.]+$/',
                'messages' => [Zend_Validate_Regex::NOT_MATCH => "Invalid characters."]
            ]]
        ];

    }

    // Form Fields //

    protected function _getHeight()
    {
        $name = 'height';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getWeight()
    {

        $name = 'weight';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getEyes()
    {

        $name = 'eyes';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getHair()
    {

        $name = 'hair';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getHairLength()
    {

        $name = 'hair_length';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getBodyType()
    {

        $name = 'body_type';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    // Shirts //

    protected function _getShirt()
    {

        $name = 'shirt';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getSleeve()
    {

        $name = 'sleeve';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getNeck()
    {

        $name = 'neck';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getJacket()
    {

        $name = 'jacket';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    // Pants //

    protected function _getWaist()
    {

        $name = 'waist';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getInseam()
    {

        $name = 'inseam';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    // Other //

    protected function _getShoes()
    {

        $name = 'shoes';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getGloves()
    {

        $name = 'gloves';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getHat()
    {

        $name = 'hat';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => $this->getStringFilters(),
            'validators' => $this->getStringValidators(),
        ];

        return new Zend_Form_Element_Text($name, $options);

    }

    protected function _getWardrobeNotes()
    {

        $name = 'wardrobe_notes';

        $options = [
            'name' => $name,
            'required' => false,
            'filters' => [
                ['StringTrim'],
                ['PregReplace', [
                    'match' => '/[^A-Za-z0-9 \'-\/\"\\,.]/',
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
                    'pattern' => '/^[A-Za-z0-9 \'-\/\"\\,.]+$/',
                    'messages' => [Zend_Validate_Regex::NOT_MATCH => "Invalid characters."]
                ]]
            ],
        ];

        return new Zend_Form_Element_Textarea($name, $options);

    }


}