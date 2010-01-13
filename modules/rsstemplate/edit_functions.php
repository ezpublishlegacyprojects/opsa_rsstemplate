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

class opsaRSSTemplateEditFunction
{
    /*!
     Store RSSExport

     \static
     \param Module
     \param HTTP
     \param publish ( true/false )
    */
    static function storeRSSExport( $Module, $http, $publish = false )
    {
        self::storeRSSExportItemsTemplate( $Module, $http );
    	return eZRSSEditFunction::storeRSSExport( $Module, $http, $publish );
    }
	
    /*!
     \public
     Static public method for store 'Use Template' fields on RSS Edit Export 
     */
    static function storeRSSExportItemsTemplate( $Module, $http )
    {
    	for( $itemCount = 0; $itemCount < $http->postVariable('Item_Count'); $itemCount++ ){
    		$rssExportItemID = $http->postVariable( "Item_ID_$itemCount" );
    		$useTplForTitle = self::processPostVariable($http, "Item_Title_Use_Template_$itemCount");
			$useTplForDesc = self::processPostVariable($http, "Item_Description_Use_Template_$itemCount");
    		echo "title: $useTplForTitle; desc: $useTplForDesc";
			
    		$rssExportItemTpl = opsaRSSExportItemTemplate::fetch( $rssExportItemID, true );
    		if ( $rssExportItemTpl == null ){
				$rssExportItemTpl = new opsaRSSExportItemTemplate( array(
					'ezrss_export_item_id' => $rssExportItemID,
					'use_template_title' => (bool) $useTplForTitle,
					'use_template_description' => (bool) $useTplForDesc
				) );

    		} else {
    			$rssExportItemTpl->setAttribute('use_template_title', (bool) $useTplForTitle);
    			$rssExportItemTpl->setAttribute('use_template_description', (bool) $useTplForDesc);
    		}
    		
    		$rssExportItemTpl->store();
    	}
    }
    
   /*!
    \private
    \return the value of the post variable
    Private helper function that validate 'Use Template' post variables
    */
    private static function processPostVariable( $http, $variable )
    {
        if ( $http->hasPostVariable( $variable ) ){
    		return $http->postVariable( $variable );
    	} else {
    		eZDebug::writeWarning( "Post variable $variable not defined", 'opsaRSSTemplate' );
    		return false;
    	}	
    }
}
?>
