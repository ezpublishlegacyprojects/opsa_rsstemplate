<?php
//
// Created on: <16-Jan-2010 08:05:08 Socrattes>
//
// SOFTWARE NAME: OPSA RSS Template Extension
// SOFTWARE RELEASE: 1.0
// BUILD VERSION:
// COPYRIGHT NOTICE: Copyright (C) Organizacion Publicitaria S.A.
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//

/*!
 \class opsaRSSExport
 \brief Extends eZRSSExport for add template support
 */
require_once( 'kernel/common/template.php' );

class opsaRSSExport extends eZRSSExport {

	static function definition()
    {
 		$RSSExportDef = parent::definition();
 		$RSSExportDef['class_name'] = 'opsaRSSExport';
 		return $RSSExportDef;

    }
	
	static function fetch( $id, $asObject = true, $status = opsaRSSExport::STATUS_VALID )
    {
    	
    	return eZPersistentObject::fetchObject( opsaRSSExport::definition(),
                                                null,
                                                array( "id" => $id,
                                                       'status' => $status ),
                                                $asObject );
    }
    
	function fetchItems( $id = false, $status = opsaRSSExport::STATUS_VALID )
    {
    
    	if ( $id === false )
        {
            if ( isset( $this ) )
            {
                $id = $this->ID;
                $status = $this->Status;
            }
            else
            {
                $itemList = null;
                return $itemList;
            }
        }
        if ( $id !== null )
            $itemList = opsaRSSExportItem::fetchFilteredList( array( 'rssexport_id' => $id, 'status' => $status ) );
        else
            $itemList = null;
        return $itemList;
    }
    
    function removeThis()
    {
        $exportItems = $this->fetchItems();

        $db = eZDB::instance();
        $db->begin();
        foreach ( $exportItems as $item )
        {
            $item->remove();
            
            if ( !opsaRSSExportItem::fetch($item->ID, true, opsaRSSExport::STATUS_VALID ) &&
            	 $exportItemTpl = opsaRSSExportItemTemplate::fetch( $item->ID, true ) ){
            	$exportItemTpl->remove(); 
            }
        }
        eZPersistentObject::remove();
        $db->commit();
    }
    
    
    function fetchRSS1_0()
    {
        $imageURL = $this->fetchImageURL();

        // Get URL Translation settings.
        $config = eZINI::instance( 'site.ini' );
        $configExt = eZINI::instance( 'opsa_rss.ini' );
        
        if ( $configExt->variable( 'URLTranslator', 'Translation' ) ){
        	if ( $configExt->variable( 'URLTranslator', 'Translation' ) == 'enabled' )
	            $useURLAlias = true;
	        else
	            $useURLAlias = false;
        }
        else
        {
	        if ( $config->variable( 'URLTranslator', 'Translation' ) == 'enabled' )
	            $useURLAlias = true;
	        else
	            $useURLAlias = false;
        }
	        
        if ( $this->attribute( 'url' ) == '' )
        {
            $baseItemURL = '';
            eZURI::transformURI( $baseItemURL, false, 'full' );
            $baseItemURL .= '/';
        }
        else
        {
            $baseItemURL = $this->attribute( 'url' ).'/'; //.$this->attribute( 'site_access' ).'/';
        }

        $doc = new DOMDocument( '1.0', 'utf-8' );
        $root = $doc->createElementNS( 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:RDF' );
        $doc->appendChild( $root );

        $channel = $doc->createElementNS( 'http://purl.org/rss/1.0/', 'channel' );
        $channel->setAttributeNS( 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:about', $this->attribute( 'url' ) );
        $root->appendChild( $channel );

        $channelTitle = $doc->createElement( 'title' );
        $channelTitle->appendChild( $doc->createTextNode( $this->attribute( 'title' ) ) );
        $channel->appendChild( $channelTitle );

        $channelUrl = $doc->createElement( 'link' );
        $channelUrl->appendChild( $doc->createTextNode( $this->attribute( 'url' ) ) );
        $channel->appendChild( $channelUrl );

        $channelDescription = $doc->createElement( 'description' );
        $channelDescription->appendChild( $doc->createTextNode( $this->attribute( 'description' ) ) );
        $channel->appendChild( $channelDescription );

        if ( $imageURL !== false )
        {
            $channelImage = $doc->createElement( 'image' );
            $channelImage->setAttributeNS( 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:resource', $imageURL );
            $channel->appendChild( $channelImage );

            $image = $doc->createElement( 'image' );
            $image->setAttributeNS( 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:about', $imageURL );

            $imageTitle = $doc->createElement( 'title' );
            $imageTitle->appendChild( $doc->createTextNode( $this->attribute( 'title' ) ) );
            $image->appendChild( $imageTitle );

            $imageLink = $doc->createElement( 'link' );
            $imageLink->appendChild( $doc->createTextNode( $this->attribute( 'url' ) ) );
            $image->appendChild( $imageLink );

            $imageUrlNode = $doc->createElement( 'url' );
            $imageUrlNode->appendChild( $doc->createTextNode( $imageURL ) );
            $image->appendChild( $imageUrlNode );

            $root->appendChild( $image );
        }

        $items = $doc->createElement( 'items' );
        $channel->appendChild( $items );

        $rdfSeq = $doc->createElementNS( 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:Seq' );
        $items->appendChild( $rdfSeq );

        $cond = array(
                    'rssexport_id'  => $this->ID,
                    'status'        => $this->Status
                    );
        $rssSources = eZRSSExportItem::fetchFilteredList( $cond );
        $nodesPerRSSItems = opsaRSSExportItem::fetchNodeList( $rssSources, $this->getObjectListFilter() );      
        
        foreach ( $nodesPerRSSItems as $nodesPerRSSItem ) {
        	$rssItemID = $nodesPerRSSItem['RSSItem'];
        	$nodeArray = $nodesPerRSSItem['Nodes'];

	        if ( is_array( $nodeArray ) && count( $nodeArray ) )
	        {
	            $attributeMappings = eZRSSExportItem::getAttributeMappings( $rssSources );
	
	            foreach ( $nodeArray as $node )
	            {
	                $object = $node->attribute( 'object' );
	                $dataMap = $object->dataMap();
	                if ( $useURLAlias === true )
	                {
	                    $nodeURL = $this->urlEncodePath( $baseItemURL . $node->urlAlias() );
	                }
	                else
	                {
	                    $nodeURL = $baseItemURL . 'content/view/full/' . $node->attribute( 'node_id' );
	                }
	
	                $rdfSeqLi = $doc->createElementNS( 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:li' );
	                $rdfSeqLi->setAttributeNS( 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:resource', $nodeURL );
	                $rdfSeq->appendChild( $rdfSeqLi );
	
	                // keep track if there's any match
	                $doesMatch = false;
	                // start mapping the class attribute to the respective RSS field
	                foreach ( $attributeMappings as $attributeMapping )
	                {
	                    // search for correct mapping by path
	                    if ( $attributeMapping[0]->attribute( 'class_id' ) == $object->attribute( 'contentclass_id' ) and
	                         in_array( $attributeMapping[0]->attribute( 'source_node_id' ), $node->attribute( 'path_array' ) ) )
	                    {
	                        // found it
	                        $doesMatch = true;
	                        // now fetch the attributes
	                        $title =  $dataMap[$attributeMapping[0]->attribute( 'title' )];
	                        $description =  $dataMap[$attributeMapping[0]->attribute( 'description' )];
	                        break;
	                    }
	                }
	
	                if( !$doesMatch )
	                {
	                    // no match
	                    eZDebug::writeWarning( __METHOD__ . ': Cannot find matching RSS source node for content object in '.__FILE__.', Line '.__LINE__ );
	                    $retValue = null;
	                    return $retValue;
	                }
	
					$itemTitleText = $this->getAttributeRSSContent( $title, $rssItemID, 'title' );					
	                $itemTitle = $doc->createElement( 'title' );
	                $itemTitle->appendChild( $doc->createTextNode( $itemTitleText ) );
	
					$itemDescriptionText = $this->getAttributeRSSContent( $description, $rssItemID, 'description' );	
	                $itemDescription = $doc->createElement( 'description' );
	                $itemDescription->appendChild( $doc->createTextNode( $itemDescriptionText ) );
	
	                $itemLink = $doc->createElement( 'link' );
	                $itemLink->appendChild( $doc->createTextNode( $nodeURL ) );
	
	                $item = $doc->createElementNS( 'http://purl.org/rss/1.0/', 'item' );
	                $item->setAttributeNS( 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'rdf:about', $nodeURL );
	
	                $item->appendChild( $itemTitle );
	                $item->appendChild( $itemLink );
	                $item->appendChild( $itemDescription );
	
	                $root->appendChild( $item );
	            }
	        }
        }

        return $doc;
    }
    
    function fetchRSS2_0()
    {
        $locale = eZLocale::instance();

        // Get URL Translation settings.
        $config = eZINI::instance( 'site.ini' );
        $configExt = eZINI::instance( 'opsa_rss.ini' );
        
        if ( $configExt->variable( 'URLTranslator', 'Translation' ) ){
        	if ( $configExt->variable( 'URLTranslator', 'Translation' ) == 'enabled' )
	            $useURLAlias = true;
	        else
	            $useURLAlias = false;
        }
        else
        {
	        if ( $config->variable( 'URLTranslator', 'Translation' ) == 'enabled' )
	            $useURLAlias = true;
	        else
	            $useURLAlias = false;
        }

        if ( $this->attribute( 'url' ) == '' )
        {
            $baseItemURL = '';
            eZURI::transformURI( $baseItemURL, false, 'full' );
            $baseItemURL .= '/';
        }
        else
        {
            $baseItemURL = $this->attribute( 'url' ).'/'; //.$this->attribute( 'site_access' ).'/';
        }

        $doc = new DOMDocument( '1.0', 'utf-8' );
        $doc->formatOutput = true;
        $root = $doc->createElement( 'rss' );
        $root->setAttribute( 'version', '2.0' );
        $root->setAttribute( 'xmlns:atom', 'http://www.w3.org/2005/Atom' );
        $doc->appendChild( $root );

        $channel = $doc->createElement( 'channel' );
        $root->appendChild( $channel );

        $atomLink = $doc->createElement( 'atom:link' );
        $atomLink->setAttribute( 'href', $baseItemURL . "rss/feed/" . $this->attribute( 'access_url' ) );
        $atomLink->setAttribute( 'rel', 'self' );
        $atomLink->setAttribute( 'type', 'application/rss+xml' );
        $channel->appendChild( $atomLink );

        $channelTitle = $doc->createElement( 'title' );
        $channelTitle->appendChild( $doc->createTextNode( $this->attribute( 'title' ) ) );
        $channel->appendChild( $channelTitle );

        $channelLink = $doc->createElement( 'link' );
        $channelLink->appendChild( $doc->createTextNode( $this->attribute( 'url' ) ) );
        $channel->appendChild( $channelLink );

        $channelDescription = $doc->createElement( 'description' );
        $channelDescription->appendChild( $doc->createTextNode( $this->attribute( 'description' ) ) );
        $channel->appendChild( $channelDescription );

        $channelLanguage = $doc->createElement( 'language' );
        $channelLanguage->appendChild( $doc->createTextNode( $locale->httpLocaleCode() ) );
        $channel->appendChild( $channelLanguage );

        $imageURL = $this->fetchImageURL();
        if ( $imageURL !== false )
        {
            $image = $doc->createElement( 'image' );

            $imageUrlNode = $doc->createElement( 'url' );
            $imageUrlNode->appendChild( $doc->createTextNode( $imageURL ) );
            $image->appendChild( $imageUrlNode );

            $imageTitle = $doc->createElement( 'title' );
            $imageTitle->appendChild( $doc->createTextNode( $this->attribute( 'title' ) ) );
            $image->appendChild( $imageTitle );

            $imageLink = $doc->createElement( 'link' );
            $imageLink->appendChild( $doc->createTextNode( $this->attribute( 'url' ) ) );
            $image->appendChild( $imageLink );

            $channel->appendChild( $image );
        }

        $cond = array(
                    'rssexport_id'  => $this->ID,
                    'status'        => $this->Status
                    );
        $rssSources = eZRSSExportItem::fetchFilteredList( $cond );

        $nodesPerRSSItems = opsaRSSExportItem::fetchNodeList( $rssSources, $this->getObjectListFilter() );      
        
        foreach ( $nodesPerRSSItems as $nodesPerRSSItem ) {
        	$rssItemID = $nodesPerRSSItem['RSSItem'];
        	$nodeArray = $nodesPerRSSItem['Nodes'];
        	
	        if ( is_array( $nodeArray ) && count( $nodeArray ) )
	        {
	            $attributeMappings = eZRSSExportItem::getAttributeMappings( $rssSources );
	
	            foreach ( $nodeArray as $node )
	            {
	                $object = $node->attribute( 'object' );
	                $dataMap = $object->dataMap();
	                if ( $useURLAlias === true )
	                {
	                    $nodeURL = $this->urlEncodePath( $baseItemURL . $node->urlAlias() );
	                }
	                else
	                {
	                    $nodeURL = $baseItemURL . 'content/view/full/' . $node->attribute( 'node_id' );
	                }
	
	                // keep track if there's any match
	                $doesMatch = false;
	                // start mapping the class attribute to the respective RSS field
	                foreach ( $attributeMappings as $attributeMapping )
	                {
	                    // search for correct mapping by path
	                    if ( $attributeMapping[0]->attribute( 'class_id' ) == $object->attribute( 'contentclass_id' ) and
	                         in_array( $attributeMapping[0]->attribute( 'source_node_id' ), $node->attribute( 'path_array' ) ) )
	                    {
	                        // found it
	                        $doesMatch = true;
	                        // now fetch the attributes
	                        $title =  $dataMap[$attributeMapping[0]->attribute( 'title' )];
	                        $description =  $dataMap[$attributeMapping[0]->attribute( 'description' )];
	                        // category is optional
	                        $catAttributeIdentifier = $attributeMapping[0]->attribute( 'category' );
	                        $category = $catAttributeIdentifier ? $dataMap[$catAttributeIdentifier] : false;
	                        break;
	                    }
	                }
	
	                if( !$doesMatch )
	                {
	                    // no match
	                    eZDebug::writeWarning( __METHOD__ . ': Cannot find matching RSS source node for content object in '.__FILE__.', Line '.__LINE__ );
	                    $retValue = null;
	                    return $retValue;
	                }
	
	                $item = $doc->createElement( 'item' );
					$itemTitleText = $this->getAttributeRSSContent( $title, $rssItemID, 'title' );					
					
	                $itemTitle = $doc->createElement( 'title' );
	                $itemTitle->appendChild( $doc->createTextNode( $itemTitleText ) );
	                $item->appendChild( $itemTitle );
	
	                $itemLink = $doc->createElement( 'link' );
	                $itemLink->appendChild( $doc->createTextNode( $nodeURL ) );
	                $item->appendChild( $itemLink );
	
	                $itemGuid = $doc->createElement( 'guid' );
	                $itemGuid->appendChild( $doc->createTextNode( $nodeURL ) );
	                $item->appendChild( $itemGuid );
	
					$itemDescriptionText = $this->getAttributeRSSContent( $description, $rssItemID, 'description' );					
	                $itemDescription = $doc->createElement( 'description' );
	                $itemDescription->appendChild( $doc->createTextNode( $itemDescriptionText ) );
	                $item->appendChild( $itemDescription );
	
	                // category RSS element with respective class attribute content
	                if ( $category )
	                {
	                    $categoryContent =  $category->attribute( 'content' );
	                    if ( $categoryContent instanceof eZXMLText )
	                    {
	                        $outputHandler = $categoryContent->attribute( 'output' );
	                        $itemCategoryText = $outputHandler->attribute( 'output_text' );
	                    }
	                    elseif ( $categoryContent instanceof eZKeyword )
	                    {
	                        $itemCategoryText = $categoryContent->keywordString();
	                    }
	                    else
	                    {
	                        $itemCategoryText = $categoryContent;
	                    }
	
	                    $itemCategory = $doc->createElement( 'category' );
	                    $itemCategory->appendChild( $doc->createTextNode( $itemCategoryText ) );
	                    $item->appendChild( $itemCategory );
	                }
	
	                $itemPubDate = $doc->createElement( 'pubDate' );
	                $itemPubDate->appendChild( $doc->createTextNode( gmdate( 'D, d M Y H:i:s', $object->attribute( 'published' ) ) .' GMT' ) );
	
	                $item->appendChild( $itemPubDate );
	
	                $channel->appendChild( $item );
	            }
	        }
    	}
        return $doc;
    }	
	
    /*!
     \private
     \return if the current RSS Item use custom template
     */
    function useCustomTemplate( $rssExportElementID, $tagRSS ){
    	
    	switch( $tagRSS ){
			case 'title':
				$templateAttr = 'use_template_title';
			break;
			
			case 'description':
				$templateAttr = 'use_template_description';
			break;
			
			default:
				throw new ezcBaseException("$tagRSS: Not a valid RSS tag element");
		}
		
		$rssItemTpl = opsaRSSExportItemTemplate::fetch( $rssExportElementID, true );
		return $rssItemTpl && $rssItemTpl->attribute( $templateAttr );
    	
    }
    
    /*!
     \private
     \return attribute content
     Helper function that get the content or fetches the specified template related with the given attribute
     */
    function getAttributeRSSContent( $attribute, $rssExportElementID, $tagRSS ){
		$opsaRSS = eZINI::instance('opsa_rss.ini');
		$blockName = 'RSSExport_'. $this->ID;
		$varName = 'Attributes';
		
		$useCustomTpl = $this->useCustomTemplate( $rssExportElementID, $tagRSS );
		
		$classObject = eZContentClass::fetch( 
			$attribute->attribute('contentclass_attribute')->attribute('contentclass_id'), true
		);
		$attributeString = $classObject->attribute('identifier') .'/'. $attribute->attribute('contentclass_attribute_identifier'); 	
		
		$confAttrs = $opsaRSS->variable( $blockName, $varName ); 
		$templateFile = null;
		foreach( $confAttrs as $attrIdentifier => $template ){
			if ( $attrIdentifier == $attributeString ){
				$templateFile = $template;
				break;
			}
		}
		
		if ( $useCustomTpl ){
			if ( $templateFile !== null ){
				$tpl = templateInit();
				$tpl->setVariable('attribute', $attribute);
				return $tpl->fetch("design:opsa_rss/attributes/$templateFile");
			}
			else {
				throw new Exception("Template not found for attribute: $attributeString");
			}
		}
		
        $attributeContent =  $attribute->attribute( 'content' );
        if ( $attributeContent instanceof eZXMLText )
        {
        	$outputHandler = $attributeContent->attribute( 'output' );
            return $outputHandler->attribute( 'output_text' );
        }
        else
        {
        	return $attributeContent;
        }
    }
    
    static function fetchByName( $access_url, $asObject = true )
    {
        return eZPersistentObject::fetchObject( self::definition(),
                                                null,
                                                array( 'access_url' => $access_url,
                                                       'active' => 1,
                                                       'status' => 1 ),
                                                $asObject );
    }
}

?>