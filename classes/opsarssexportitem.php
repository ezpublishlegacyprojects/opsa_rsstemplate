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
 \class opsaRSSExportItem
 \brief Extends eZRSSExportItem for add template support
 */
class opsaRSSExportItem extends eZRSSExportItem {

	static function definition()
    {
        $RSSExportItemDef = parent::definition();
        $RSSExportItemDef['class_name'] = 'opsaRSSExportItem';
        $RSSExportItemDef['function_attributes']['use_template'] = 'useTemplate';
        return $RSSExportItemDef;
    }  
    
    /*!
     \return attribute content
     Function attribute that return the value of use_template attribute
     */
    function useTemplate()
    {   	
    	$objDef = self::definition();
    	    	
    	$db = eZDB::instance();
    	$rows = $db->arrayQuery('
    		Select IF( T1.use_template_title IS NULL, FALSE, T1.use_template_title ) as title,
    			   IF( T1.use_template_description IS NULL, FALSE, T1.use_template_description ) as description
    		From '. $objDef['name']. ' as T0 
    		Left Join opsarss_export_item_template as T1
    		On T0.id = T1.ezrss_export_item_id
    		Where T0.id = '. $this->ID.'
    	');
    	
    	return $rows[0];
    }
    
    static function fetchFilteredList( $cond, $asObject = true, $status = eZRSSExport::STATUS_VALID )
    {
        return eZPersistentObject::fetchObjectList( self::definition(),
                                                    null, $cond, array( 'id' => 'asc',
                                                                        'status' => $status ), null,
                                                    $asObject );
    }
    
    static function fetchNodeList( $rssSources, $objectListFilter )
    {    	
    	$nodesPerRSSItem = array();
    	
    	// compose parameters for several subtrees
        if( is_array( $rssSources ) && count( $rssSources ) )
        {
            foreach( $rssSources as $rssSource )
            {
                // Do not include subnodes
                if ( !intval( $rssSource->Subnodes ) )
                {
                    $depth = 1;
                }
                else // Fetch objects even from subnodes
                {
                    $depth = 0;
                }

                $params = array('Depth' => $depth,
                                'DepthOperator' => 'eq',
                                'MainNodeOnly' => $objectListFilter['main_node_only'],
                                'ClassFilterType' => 'include',
                                'ClassFilterArray' => array( intval( $rssSource->ClassID ) ),
                				'Limit' => $objectListFilter['number_of_objects'],
                                'SortBy' => array( 'published', false ) );
                                       
	
	            $nodeList = eZContentObjectTreeNode::subTreeByNodeID( $params, $rssSource->SourceNodeID );				
                                       
				$nodesPerRSSItem[] = array( 'RSSItem' => $rssSource->ID,
											'Nodes' => $nodeList );
            }
        }
        else
            $nodesPerRSSItem = null;
        return $nodesPerRSSItem;
    }
    
	
}

?>