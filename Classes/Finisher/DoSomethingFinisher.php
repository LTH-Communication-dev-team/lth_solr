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

        $settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['lth_solr']);

        $image = $this->getMail()->getAnswersByFieldMarker()['image']->getValue();
        $email = $this->getMail()->getAnswersByFieldMarker()['email']->getValue();
        if($image && $email) {
            $image = 'fileadmin/images/uploads/' . addslashes($image[0]);
            $email = addslashes($email);
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "email='$email'", array('image' => $image));
            
            $this->updateSolr($settings, 'sv', $email, $image);
            $this->updateSolr($settings, 'en', $email, $image);
            
        }
    }
    
    public function updateSolr($settings, $syslang, $email, $image)
    {
        $config = array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $settings['solrHost'],
                    'port' => $settings['solrPort'],
                    'path' => "/solr/core_$syslang/",//$settings['solrPath'],
                    'timeout' => $settings['solrTimeout']
                )
            )
        );
        $client = new \Solarium\Client($config);
        $query = $client->createSelect();
        $query->setQuery('email:'.$email);
        $response = $client->select($query);
        foreach ($response as $document) {
            $id = $document->id;
        }
        if($id) {
            $update = $client->createUpdate();
            ${"doc"} = $update->createDocument(); 
            ${"doc"}->setKey('id', $id);
            ${"doc"}->addField('image', $image);
            ${"doc"}->setFieldModifier('image', 'set');
            ${"doc"}->addField('appKey', 'lthsolr');
            ${"doc"}->setFieldModifier('appKey', 'set');
            $docArray[] = ${"doc"};
            $update->addDocuments($docArray);
            $update->addCommit();
            $result = $client->update($update);
        }
    }
}