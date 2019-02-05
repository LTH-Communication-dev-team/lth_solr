<?php

namespace Lth\Lthsolr\Finisher;

use In2code\Powermail\Finisher\AbstractFinisher;

/**
 * Class DoSomethingFinisher
 *
 * @package Vendor\Ext\Finisher
 */
class DoSomethingFinisher extends AbstractFinisher
{

    /**
     * MyFinisher
     *
     * @return void
     */
    public function myFinisher()
    {
        $image = $this->getMail()->getAnswersByFieldMarker()['image']->getValue();
        $email = $this->getMail()->getAnswersByFieldMarker()['email']->getValue();
        if($image && $email) {
            $image = 'fileadmin/images/uploads/' . addslashes($image[0]);
            $email = addslashes($email);
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', 'email='.$email, array('email' => $email, 'name' => $name));
        }
    }
}