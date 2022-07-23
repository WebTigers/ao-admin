<?php

class AOModelResponse
{
    /**
     * Result
     *
     * Often the result is simply an boolean or integer response.
     *
     * @var mixed
     */
    public $result;

    /**
     * Used for any type of response data to be consumed by the call.
     *
     * @var mixed
     */
    public $data;

    /**
     * Used for form validation.
     *
     * @var array
     */
    public $form;

    /**
     * Used for form validation element identity
     *
     * @var string
     */
    public $element;

    /**
     * Used for form validation form or element messages
     * @var array
     */
    public $messages;

    /**
     * Response text
     *
     * @var array of text strings
     */
    public $text = [];

    /**
     * Response HTML strings
     *
     * @var string
     */
    public $html = [];

    const MESSAGE_CLASS_ALERT = 3;
    const MESSAGE_CLASS_ERROR = 2;
    const MESSAGE_CLASS_SUCCESS = 1;
    const MESSAGE_CLASS_INFO = 0;

    public function __construct( array $params = [] )
    {
        foreach ( $params as $key => $value) {
            $this->$key = $value;
        }
    }

    public function setTextMessage ( $message, $type = 3 )
    {

        $this->text[] = $message;
        $this->html[] = $this->createHTMLMessage( $message, $type );

    }

    /**
     *
     * @param mixed $message object or sting
     * @param mixed $type (see switch statement for types)
     * @return string '<div class="alert alert-info"><i class="icon-line-alert-triangle"></i>Some alert info message.</div>'
     */
    public function createHTMLMessage ( $message, $type = 3 )
    {

        $out_message    = ( is_object( $message ) ) ? $message->message : $message;
        $out_type       = ( is_object( $message ) ) ? $message->class   : $type;

        switch ( $out_type ) {

            case 3:
            case 'alert':
                $class = 'alert alert-warning';
                $icon  = 'icon-line-alert-triangle';
                break;

            case 2:
            case 'error':
                $class = 'alert alert-danger';
                $icon  = 'icon-line-alert-circle';
                break;

            case 1:
            case 'success':
                $class = 'alert alert-success';
                $icon  = 'icon-line-circle-check';
                break;

            case 0:
            case 'info':
            default :
                $class = 'alert alert-info';
                $icon  = 'icon-line-info';
                break;

        }

        /**
        <div class="alert alert-warning" role="alert">
            <i class="icon-line-alert-triangle"></i>
            This is a warning message!
        </div>
         */

        return  '<div class="' . $class . '"><i class="' . $icon . '"></i>' . $out_message . '</div>';

    }

    /**
     * Returns an array of the public response properties.
     * @return array
     */
    public function toArray ( )
    {
        return [
            'result'    => $this->result,
            'data'      => $this->data,
            'form'      => $this->form,
            'element'   => $this->element,
            'messages'  => $this->messages,
            'text'      => $this->text,
            'html'      => $this->html,
        ];
    }

    public function clearMessages ( )
    {
        $this->text = [];
        $this->html = [];

        return $this;
    }

}