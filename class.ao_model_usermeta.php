<?php

class AOModelUserMeta extends Zend_Db_Table_Abstract
{
    public function init( )
    {
    }

    public function getUserMeta ( $user_id, $meta_key )
    {
        $sql =$this->select()->
            where( 'user_id = ?', $user_id)->
            where( 'meta_key = ?', $meta_key );

        return $this->fetchRow( $sql );
    }

    public function updateUserMeta ( $user_id, $meta_key, $meta_value )
    {
        $userMetaRow = $this->getUserMeta ( $user_id, $meta_key );

        if ( empty( $userMetaRow ) ) {

            $this->createRow([
                'user_id' => $user_id,
                'meta_key' => $meta_key,
                'meta_value' => $meta_value
            ])->save();

        }
        else {

            $userMetaRow->setFromArray([ 'meta_value' => $meta_value ])->save();

        }

    }

}