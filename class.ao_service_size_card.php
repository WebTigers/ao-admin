<?php

class AOServiceSizeCard {

    protected $response;
    protected $form;

    public function __construct ( $request = null ) {

        $this->response = new AOModelResponse();
        $this->form     = new AOFormSizeCard();

        if ( ! empty( $request['method'] ) && method_exists( $this, $request['method'], ) ){
            $this->{$request['method']}( $request );
        }

    }

    public function getResponse ( ) {

        return $this->response;

    }

    public function save ( $request )
    {

        // Unset boilerplate vars
        $data = $request['data'];

        if ( $this->form->isValid( $data ) ) {

            $userId     = get_current_user_id();                            // int
            $formName   = $this->form->getName();                           // string
            $cleanData  = $this->form->getValues();                         // array
            $jsonData   = json_encode( $cleanData, JSON_HEX_QUOT );   // string

            update_user_meta( $userId, $formName, $jsonData );

            // Sends a success response //

            $this->response->result = 1;
            $this->response->form = $this->form->getName();
            $this->response->setTextMessage( 'Your size card has been saved.', 'success' );
            $this->response->messages = [];

        } else {

            // Invalid Entry //

            $this->response->result = 0;
            $this->response->form = $this->form->getName();
            $this->response->setTextMessage( 'There are errors on your form.', 'error' );
            $this->response->messages = $this->form->getMessages();

        }

    }

    protected function setElementMessages ( Zend_Form_Element $element ) {

        $messages = $element->getMessages();

        foreach ( $messages as $error => $message ) {

            $this->response->setTextMessage( $message, 'error' );

        }

    }

    public function getFormData ( $request ) {

        $userId = get_current_user_id();    // int

        $data = get_user_meta( $userId, 'AOFormSizeCard', true );

        $this->response->result = 1;
        $this->response->form = $this->form->getName();
        $this->response->data = json_decode( $data );

    }


}