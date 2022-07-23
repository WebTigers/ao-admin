<?php

class AOServiceValidation {

    protected $response;

    public function __construct ( $request = null ) {

        $this->response = new AOModelResponse();

        if ( ! empty( $request['method'] ) && method_exists( $this, $request['method'], ) ){
            $this->{$request['method']}( $request );
        }

    }

    public function getResponse ( ) {

        return $this->response;

    }

    public function validate ( $request )
    {

        $form = new $request['form']();     // ie. AOFormProfile
        $element = $form->getElement($request['element']);


        if ( $element instanceof Zend_Form_Element ) {

            if ( $element->isValid($request['value'], $request) ) {

                // Sends an empty response //

                $this->response->result = 1;
                $this->response->form = $form->getName();
                $this->response->element = $request['element'];
                $this->response->messages = [];


            } else {

                // Invalid Entry //

                $this->response->result = 0;
                $this->response->form = $form->getName();
                $this->response->element = $request['element'];
                $this->response->messages[ $request['element'] ] = $element->getMessages();

            }

        }

    }

}